<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

use App\Models\User;
use App\Models\Member;
use App\Models\Campaign;
use App\Models\Action;
use App\Models\Role;

class TestingSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        $pastcampaign = Campaign::factory()->create();
        $currentcampaign = Campaign::factory()->current()->create();
        
        for ($i=1000;$i<=1100;$i++) {
            $user = User::factory()->create([
                'username' => $i
            ]);
            switch ($i) {
            case 1020:
            case 1030:
                // set specific members
                $member = Member::factory()->create([
                    'membership' => $i,
                    'department' => 'Philosophy',
                    'voter' => true,
                ]);
                break;
            case 1040:
                $member = Member::factory()->create([
                    'membership' => $i,
                    'voter' => true,
                ]);
                break;
            default:
                // randomise
                $member = Member::factory()->create([
                    'membership' => $i
                ]);
            }
            if ($member->voter) {
                switch ($member->id) {
                case 1020:
                    // force no actions
                    break;
                case 1030:
                    // force yes action this time
                    Action::factory()->create([
                        'member_id' => $member->id,
                        'campaign_id' => $currentcampaign->id,
                        'action' => 'yes'
                    ]);
                    break;
                default:
                    if (rand(0,10) < 5) {
                        Action::factory()->create([
                            'member_id' => $member->id,
                            'campaign_id' => $pastcampaign->id,
                        ]);
                        if (rand(0,10) < 3) {
                            Action::factory()->create([
                                'member_id' => $member->id,
                                'campaign_id' => $currentcampaign->id,
                            ]);
                        }
                    } elseif (rand(0,10) < 2) {
                        Action::factory()->create([
                            'member_id' => $member->id,
                            'campaign_id' => $currentcampaign->id,
                        ]);
                    }
                }
            }
            /* Set up roles */
            switch ($i) {
            case 1000:
                Role::factory()->create([
                    'member_id' => $member->id,
                    'role' => Role::ROLE_SUPERUSER
                ]);
                break;
            case 1001:
                Role::factory()->create([
                    'member_id' => $member->id,
                    'role' => Role::ROLE_REP,
                    'restrictfield' => 'department',
                    'restrictvalue' => 'Philosophy'
                ]);
                break;
            case 1002:
                Role::factory()->create([
                    'member_id' => $member->id,
                    'role' => Role::ROLE_REP,
                    'restrictfield' => 'department',
                    'restrictvalue' => 'Library'
                ]);
                Role::factory()->create([
                    'member_id' => $member->id,
                    'role' => Role::ROLE_PHONEBANK,
                    'restrictfield' => '',
                    'restrictvalue' => ''
                ]);
                break;
            case 1003:
                Role::factory()->create([
                    'member_id' => $member->id,
                    'role' => Role::ROLE_PHONEBANK,
                    'restrictfield' => '',
                    'restrictvalue' => ''
                ]);
                break;
            case 1004:
                Role::factory()->create([
                    'member_id' => $member->id,
                    'role' => Role::ROLE_REP,
                    'restrictfield' => 'jobtype',
                    'restrictvalue' => 'Postgraduate'
                ]);
                break;
            case 1005:
                Role::factory()->create([
                    'member_id' => $member->id,
                    'role' => Role::ROLE_CAMPAIGNER,
                    'restrictfield' => 'department',
                    'restrictvalue' => 'Chemistry'
                ]);
                break;
            case 1006:
                Role::factory()->create([
                    'member_id' => $member->id,
                    'role' => Role::ROLE_PHONEBANK,
                    'restrictfield' => 'department',
                    'restrictvalue' => 'Library'
                ]);
                break;
            default:
                // no roles
            }
        }

        $pastcampaign->calctarget = ceil(Member::voter()->count()/2);
        $pastcampaign->save();
        $currentcampaign->calctarget = ceil(Member::voter()->count()/2);
        $currentcampaign->save();
    }
}
