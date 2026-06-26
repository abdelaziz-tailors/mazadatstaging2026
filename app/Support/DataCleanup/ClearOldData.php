<?php

namespace App\Support\DataCleanup;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class ClearOldData
{
    public const CONFIRMATION_TOKEN = 'DELETE_OLD_DATA';

    /**
     * Usage from tinker:
     * 1) Preview:
     *    \App\Support\DataCleanup\ClearOldData::run(dryRun: true);
     *
     * 2) Execute:
     *    \App\Support\DataCleanup\ClearOldData::run(confirm: 'DELETE_OLD_DATA');
     */
    public static function run(?string $confirm = null, bool $dryRun = false): array
    {
        $counts = self::counts();

        if ($dryRun) {
            return [
                'mode' => 'dry-run',
                'confirmation_token_required' => self::CONFIRMATION_TOKEN,
                'counts' => $counts,
            ];
        }

        if ($confirm !== self::CONFIRMATION_TOKEN) {
            throw new RuntimeException(
                'Deletion blocked. Pass confirm: "'.self::CONFIRMATION_TOKEN.'" to execute.'
            );
        }

        $deleted = [];

        DB::transaction(function () use (&$deleted): void {
            self::detachCategoriesFromPartners($deleted);
            self::deleteAuctionData($deleted);
            self::deleteSellerSubmissions($deleted);
            self::deleteSocialVideoData($deleted);
            self::deleteUserActivityData($deleted);
            self::deleteMiscTransactionalData($deleted);
            self::deleteUsers($deleted);
            self::deletePartners($deleted);
        });

        return [
            'mode' => 'delete',
            'deleted' => $deleted,
            'before' => $counts,
            'after' => self::counts(),
        ];
    }

    public static function counts(): array
    {
        return [
            'kept_admins' => DB::table('admins')->where('type', 'admin')->count(),
            'kept_categories' => DB::table('categories')->count(),
            'partners_to_delete' => DB::table('admins')->where('type', 'partner')->count(),
            'users_to_delete' => self::tableCount('users'),
            'vendors_to_delete' => DB::table('users')->where('user_type', 'vendor')->count(),
            'auctions_to_delete' => self::tableCount('live_videos'),
            'auction_products_to_delete' => self::tableCount('live_video_items'),
            'transactions_to_delete' => self::tableCount('transaction'),
        ];
    }

    private static function detachCategoriesFromPartners(array &$deleted): void
    {
        $partnerAdminIds = DB::table('admins')
            ->where('type', 'partner')
            ->pluck('id');

        if ($partnerAdminIds->isEmpty()) {
            $deleted['categories_detached'] = 0;

            return;
        }

        $deleted['categories_detached'] = DB::table('categories')
            ->whereIn('admin_id', $partnerAdminIds)
            ->update(['admin_id' => null]);
    }

    private static function deleteAuctionData(array &$deleted): void
    {
        self::deleteFromTable('video_comment_likes', $deleted);
        self::deleteFromTable('video_comments', $deleted);
        self::deleteFromTable('live_video_comments', $deleted);
        self::deleteFromTable('wallet_transactions', $deleted);
        self::deleteFromTable('order_items', $deleted);
        self::deleteFromTable('orders', $deleted);
        self::deleteFromTable('user_gifts', $deleted);
        self::deleteFromTable('live_video_likes', $deleted);
        self::deleteFromTable('live_video_users', $deleted);
        self::deleteFromTable('live_video_items', $deleted);
        self::deleteFromTable('live_videos', $deleted);
    }

    private static function deleteSellerSubmissions(array &$deleted): void
    {
        self::deleteFromTable('seller_submission_media', $deleted);
        self::deleteFromTable('seller_submissions', $deleted);
    }

    private static function deleteSocialVideoData(array &$deleted): void
    {
        self::deleteFromTable('video_hashtag', $deleted);
        self::deleteFromTable('video_likes', $deleted);
        self::deleteFromTable('video_favorites', $deleted);
        self::deleteFromTable('video_reports', $deleted);
        self::deleteFromTable('video_views', $deleted);
        self::deleteFromTable('video_shares', $deleted);
        self::deleteFromTable('videos', $deleted);
    }

    private static function deleteUserActivityData(array &$deleted): void
    {
        self::deleteFromTable('store_views', $deleted);
        self::deleteFromTable('stores', $deleted);
        self::deleteFromTable('user_subscriptions', $deleted);
        self::deleteFromTable('user_coins', $deleted);
        self::deleteFromTable('user_blocks', $deleted);
        self::deleteFromTable('follow_users', $deleted);
        self::deleteFromTable('friends', $deleted);
        self::deleteFromTable('profile_views', $deleted);
        self::deleteFromTable('report_users', $deleted);
        self::deleteFromTable('user_reports', $deleted);
        self::deleteFromTable('oauth_refresh_tokens', $deleted);
        self::deleteFromTable('oauth_access_tokens', $deleted);
        self::deleteFromTable('oauth_auth_codes', $deleted);
        self::deleteFromTable('personal_access_tokens', $deleted);
        self::deleteFromTable('user_otps', $deleted);
        self::deleteFromTable('notifications', $deleted);
    }

    private static function deleteMiscTransactionalData(array &$deleted): void
    {
        self::deleteFromTable('transaction', $deleted);
        self::deleteFromTable('contact_messages', $deleted);
    }

    private static function deleteUsers(array &$deleted): void
    {
        if (! Schema::hasTable('users')) {
            $deleted['users'] = 0;

            return;
        }

        DB::table('admins')->update(['user_id' => null]);

        self::deleteModelPermissions('users', $deleted, 'users_permissions');
        $deleted['users'] = DB::table('users')->delete();
    }

    private static function deletePartners(array &$deleted): void
    {
        $partnerIds = DB::table('admins')
            ->where('type', 'partner')
            ->pluck('id');

        if ($partnerIds->isEmpty()) {
            $deleted['partners'] = 0;

            return;
        }

        self::deleteModelPermissions('admins', $deleted, 'partners_permissions', $partnerIds);

        $deleted['partners'] = DB::table('admins')
            ->whereIn('id', $partnerIds)
            ->delete();
    }

    private static function deleteModelPermissions(
        string $modelType,
        array &$deleted,
        string $counterKey,
        $modelIds = null
    ): void {
        if (! Schema::hasTable('model_has_roles') && ! Schema::hasTable('model_has_permissions')) {
            $deleted[$counterKey] = 0;

            return;
        }

        $morphClass = 'App\\Models\\'.($modelType === 'users' ? 'User\\User' : 'Admin');
        $deletedCount = 0;

        if (Schema::hasTable('model_has_roles')) {
            $query = DB::table('model_has_roles')->where('model_type', $morphClass);
            if ($modelIds !== null) {
                $query->whereIn('model_id', $modelIds);
            }
            $deletedCount += $query->delete();
        }

        if (Schema::hasTable('model_has_permissions')) {
            $query = DB::table('model_has_permissions')->where('model_type', $morphClass);
            if ($modelIds !== null) {
                $query->whereIn('model_id', $modelIds);
            }
            $deletedCount += $query->delete();
        }

        $deleted[$counterKey] = $deletedCount;
    }

    private static function deleteFromTable(string $table, array &$deleted): void
    {
        if (! Schema::hasTable($table)) {
            $deleted[$table] = 0;

            return;
        }

        $deleted[$table] = DB::table($table)->delete();
    }

    private static function tableCount(string $table): int
    {
        return Schema::hasTable($table) ? DB::table($table)->count() : 0;
    }
}
