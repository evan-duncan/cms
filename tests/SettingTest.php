<?php

use PHPUnit\Framework\TestCase;

/**
 * Runs against the database in DATABASE_DSN (cms_test, see phpunit.xml).
 * Each test runs in a transaction that is rolled back, so rows do not leak.
 */
final class SettingTest extends TestCase
{
    protected function setUp(): void
    {
        Db::conn()->beginTransaction();
    }

    protected function tearDown(): void
    {
        Db::conn()->rollBack();
    }

    public function testSetOverwritesAndGetReadsBack(): void
    {
        Setting::set('site_name', 'First');
        $this->assertSame('First', Setting::get('site_name'));

        Setting::set('site_name', 'Second');
        $this->assertSame('Second', Setting::get('site_name'));
    }

    public function testGetFallsBackToDefaultForAnUnknownKey(): void
    {
        $this->assertSame('cms', Setting::get('missing_key', 'cms'));
    }
}
