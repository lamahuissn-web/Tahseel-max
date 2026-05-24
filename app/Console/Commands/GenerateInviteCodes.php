<?php

namespace App\Console\Commands;
use Illuminate\Support\Str;
use App\Models\Members;
use Illuminate\Console\Command;

class GenerateInviteCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invite_codes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate unique invite codes for all existing members';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $members = Members::whereNull('invite_code')->get();
        foreach ($members as $member) {
            $inviteCode = 'Rashaketik-' . strtoupper(Str::random(4)) . rand(1000, 9999);
            $member->invite_code = $inviteCode;
            $member->save();
        }

        $this->info('Invite codes generated for members without an invite code!');
    }

}
