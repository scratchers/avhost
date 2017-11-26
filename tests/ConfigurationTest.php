<?php

use PHPUnit\Framework\TestCase;
use jpuck\avhost\Core\Configuration;

class ConfigurationTest extends TestCase
{
    protected static $tmp = '/tmp';

    public static function setUpBeforeClass()
    {
        $tmp = static::$tmp;
        if (!is_dir($tmp)) {
            throw new Exception("$tmp is not a directory.");
        }
    }

    public function virtualHostConfigurationDataProvider()
    {
        $tmp = static::$tmp;
        return [
            'plain' => ['example.com', $tmp],
            'ssl' => ['ssl.example.com', $tmp, [
                    'meta' => [
                        'realpaths' => false,
                    ],
                    'ssl' => [
                        'key' => '/etc/ssl/private/ssl.example.com.key',
                        'certificate' => '/etc/ssl/certs/ssl.example.com.pem',
                        'required' => true,
                    ],
                ],
            ],
            'override' => ['override.example.com', $tmp, [
                    'override' => 'All',
                ],
            ],
        ];
    }

    /**
     * @dataProvider virtualHostConfigurationDataProvider
     */
    public function test_can_generate_virtual_host_configuration_file($name, $root, $options = [])
    {
        $expected = file_get_contents(__DIR__."/fixtures/$name.conf");

        $actual = (string)(new Configuration($name, $root, $options));

        $this->assertEquals($expected, $actual);
    }

    public function arrayConfigurationDataProvider()
    {
        return [
            'plain' => [[
                'hostname' => 'example.com',
                'documentRoot' => static::$tmp,
            ]],
            'ssl' => [[
                'hostname' => 'ssl.example.com',
                'documentRoot' => '/var/www/html',
                'meta' => [
                    'realpaths' => false,
                ],
                'ssl' => [
                    'key' => '/etc/ssl/private/ssl.example.com.key',
                    'certificate' => '/etc/ssl/certs/ssl.example.com.pem',
                    'required' => true,
                ],
                'signature' => [
                    'version' => '1.0.1',
                    'createdAt' => '2017-11-24T21:56:15+00:00',
                    'createdBy' => 'jeff@xervo',
                ],
            ]],
        ];
    }

    /**
     * @dataProvider arrayConfigurationDataProvider
     */
    public function test_can_cast_to_json(array $expected)
    {
        $configuration = Configuration::createFromArray($expected);

        $actual = json_decode($configuration->toJson(), true);

        $this->assertArraySubset($expected, $actual);
    }

    /**
     * @dataProvider arrayConfigurationDataProvider
     */
    public function test_can_import_and_export(array $expected)
    {
        $configuration = Configuration::createFromArray($expected);

        $exported = $configuration->toArray();

        $imported = Configuration::createFromArray($exported);

        $this->assertArraySubset($expected, $imported->toArray());
    }
}
