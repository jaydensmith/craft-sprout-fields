<?php
/**
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

namespace barrelstrength\sproutfields\migrations;

use barrelstrength\sproutbasefields\migrations\m190313_000000_fix_non_abbreviation_administrative_codes;
use craft\db\Migration;

/**
 * m190313_000001_fix_non_abbreviation_administrative_codes_sproutfields migration.
 */
class m190313_000001_fix_non_abbreviation_administrative_codes_sproutfields extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $migration = new m190313_000000_fix_non_abbreviation_administrative_codes();

        ob_start();
        $migration->safeUp();
        ob_end_clean();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m190313_000001_fix_non_abbreviation_administrative_codes_sproutfields cannot be reverted.\n";

        return false;
    }
}
