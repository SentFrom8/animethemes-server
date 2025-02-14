<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs\Auth;

use App\Constants\Config\FlagConstants;
use App\Jobs\SendDiscordNotificationJob;
use App\Models\Auth\Invitation;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

/**
 * Class InvitationTest.
 */
class InvitationTest extends TestCase
{
    /**
     * When an invitation is created, a SendDiscordNotification job shall be dispatched.
     *
     * @return void
     */
    public function testInvitationCreatedSendsDiscordNotification(): void
    {
        Config::set(FlagConstants::ALLOW_DISCORD_NOTIFICATIONS_FLAG_QUALIFIED, true);
        Bus::fake(SendDiscordNotificationJob::class);

        Invitation::factory()->createOne();

        Bus::assertDispatched(SendDiscordNotificationJob::class);
    }

    /**
     * When an invitation is deleted, a SendDiscordNotification job shall be dispatched.
     *
     * @return void
     */
    public function testInvitationDeletedSendsDiscordNotification(): void
    {
        $invitation = Invitation::factory()->createOne();

        Config::set(FlagConstants::ALLOW_DISCORD_NOTIFICATIONS_FLAG_QUALIFIED, true);
        Bus::fake(SendDiscordNotificationJob::class);

        $invitation->delete();

        Bus::assertDispatched(SendDiscordNotificationJob::class);
    }

    /**
     * When an invitation is restored, a SendDiscordNotification job shall be dispatched.
     *
     * @return void
     */
    public function testInvitationRestoredSendsDiscordNotification(): void
    {
        $invitation = Invitation::factory()->createOne();

        Config::set(FlagConstants::ALLOW_DISCORD_NOTIFICATIONS_FLAG_QUALIFIED, true);
        Bus::fake(SendDiscordNotificationJob::class);

        $invitation->restore();

        Bus::assertDispatched(SendDiscordNotificationJob::class);
    }

    /**
     * When an invitation is updated, a SendDiscordNotification job shall be dispatched.
     *
     * @return void
     */
    public function testInvitationUpdatedSendsDiscordNotification(): void
    {
        $invitation = Invitation::factory()->createOne();

        Config::set(FlagConstants::ALLOW_DISCORD_NOTIFICATIONS_FLAG_QUALIFIED, true);
        Bus::fake(SendDiscordNotificationJob::class);

        $changes = Invitation::factory()->makeOne();

        $invitation->fill($changes->getAttributes());
        $invitation->save();

        Bus::assertDispatched(SendDiscordNotificationJob::class);
    }
}
