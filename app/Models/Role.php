<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    /* Superuser: set up roles, set up campaigns, do member imports, run reports */
    public const ROLE_SUPERUSER = 'superuser';

    /* Secretary: edit documents */
    public const ROLE_SECRETARY = 'secretary';

    /* Rep: run reports and charts */
    public const ROLE_REP = 'rep';
    /* Campaigner: same permissions as a rep, but only when campaigns are active */
    public const ROLE_CAMPAIGNER = 'campaigner';
    /* Phonebank: set campaign participation */
    public const ROLE_PHONEBANK = 'phonebank';
    /* Report: view high-level reports only */
    public const ROLE_REPORT = 'report';

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public static function roleTypes()
    {
        return [
            self::ROLE_REP => 'Representative',
            self::ROLE_CAMPAIGNER => 'Campaigner',
            self::ROLE_PHONEBANK => 'Phonebank',
            self::ROLE_REPORT => 'Reporting View',
            self::ROLE_SECRETARY => 'Secretary',
            self::ROLE_SUPERUSER => 'Super-user',
        ];
    }

    public static function roleFields()
    {
        return [
            '' => 'Unrestricted',
            'department' => 'Department',
            'jobtype' => 'Job Type',
            'membertype' => 'Member Type',
            'workplace' => 'Workplace',
        ];
    }
}
