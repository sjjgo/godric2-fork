<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Member;
use App\Models\Action;
use App\Models\Changelog;
use Carbon\Carbon;

class ImportController extends Controller
{
    private $orgtypes = ['UCUBranch'];

    public function index()
    {
        $orgtype = config('membership.orgtype');
        if (!in_array($orgtype, $this->orgtypes)) {
            return view('import.unsupported');
        }

        $changelogs = Changelog::orderBy('created_at')->get();

        return view('import.index', [
            'changelogs' => $changelogs,
        ]);
    }

    public function process(Request $request)
    {
        $orgtype = config('membership.orgtype');
        if (!in_array($orgtype, $this->orgtypes)) {
            return view('import.unsupported');
        }

        if (!$request->hasFile('list') || !$request->file('list')->isValid()) {
            return back()->with('message', 'File upload invalid');
        }

        $contents = explode("\n", trim($request->file('list')->getContent()));

        $test = str_getcsv(trim($contents[0]));
        // check expected format
        if (
            count($test) < 31 ||
            $test[0] != 'person_id' ||
            $test[30] != 'ede_primary'
        ) {
            return back()->with(
                'message',
                'File is not in the expected format'
            );
        }

        $added = [];
        $removed = [];

        for ($i = 1; $i < count($contents); $i++) {
            $line = str_getcsv(
                iconv('ISO-8859-1', 'UTF-8', trim($contents[$i]))
            );
            $member = Member::where('membership', $line[0])->first();

            if (!$member) {
                $member = new Member();
                $member->membership = $line[0];
                $added[] = $member;
            }

            $member->firstname = $line[3];
            $member->lastname = $line[4];
            $member->email = $line[7];
            $member->department = $line[10];
            $member->mobile = $line[16] ? $line[16] : $line[14];
            $member->jobtype = $line[19];
            $member->membertype = $line[29];
            if (
                ($line[29] != 'Standard' && $line[29] != 'Standard Free') ||
                $line[27] != 'N'
            ) {
                $member->voter = false;
            } else {
                $member->voter = true;
            }
            // update timestamp even if no changes made
            $member->updated_at = Carbon::now();
            $member->save();
        }

        $staff = collect(config('membership.staff'));

        $removed = Member::where('updated_at', '<', Carbon::parse('-1 hour'))
            ->whereNotIn('membership', $staff)
            ->get();

        $lister = function ($item, $key) {
            return $item->membership .
                ': ' .
                $item->firstname .
                ' ' .
                $item->lastname .
                ' (' .
                $item->email .
                ', ' .
                $item->department .
                ')';
        };

        $addlist = collect($added)->map($lister);
        $remlist = $removed->map($lister);

        // clear old changelogs
        Changelog::old()->delete();
        // save messages to changelog
        foreach ($addlist as $message) {
            $c = new Changelog();
            $c->message = 'New: ' . $message;
            $c->save();
        }
        foreach ($remlist as $message) {
            $c = new Changelog();
            $c->message = 'Removed: ' . $message;
            $c->save();
        }

        foreach ($removed as $remove) {
            $remove->actions()->delete();
            $remove->workplaces()->detach();
            $remove->ballots()->detach();
            $remove->roles()->delete();
            $remove->delete();
        }

        $excelcheck = false;
        /* Does it look as if the members file has been mangled
         * through Excel before import? */
        if (Member::where('mobile', 'LIKE', '0%')->count() == 0) {
            $excelcheck = true;
        } elseif (Member::where('mobile', 'LIKE', '%.%E+%')->count() > 0) {
            $excelcheck = true;
        }

        return view('import.process', [
            'total' => count($contents) - 1,
            'added' => $addlist,
            'removed' => $remlist,
            'excelcheck' => $excelcheck,
        ]);
    }
}
