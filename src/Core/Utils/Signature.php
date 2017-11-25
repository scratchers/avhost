<?php

namespace jpuck\avhost\Core\Utils;

use jpuck\avhost\Core\Contracts\Exportable;
use jpuck\avhost\Core\Traits\SerializeJsonFromArray;
use jpuck\avhost\Core\Configuration;

class Signature implements Exportable
{
    use SerializeJsonFromArray;

    protected $configuration;
    protected $attributes;

    protected $header = <<<HEADER
##########################################
# Generated by avhost
# https://github.com/jpuck/avhost

HEADER;

    protected $footer = <<<FOOTER
##########################################

FOOTER;

    public function __construct(Configuration $configuration, array $attributes = [])
    {
        $this->configuration = $configuration;

        $this->setDefaultAttributes();

        if ($attributes) {
            $this->setAttributes($attributes);
        }
    }

    public function setDefaultAttributes()
    {
        $this->attributes = [
            'version' => (new Version)->getVersion(),
            'createdAt' => date('c'),
            'createdBy' => trim(`whoami`) . '@' . gethostname(),
        ];
    }

    public function setAttributes(array $attributes)
    {
        foreach ($this->attributes as $key => &$value) {
            $value = $attributes[$key] ?? $value;
        }
    }

    public function toArray() : array
    {
        return array_merge($this->attributes, [
            'contentHash' => $this->configuration->getContentHash(),
        ]);
    }

    public function render() : string
    {
        $string = '';
        foreach ($this->attributes as $key => $value) {
            $string .= $this->getKeyValueLine($key, $value);
        }

        return $this->header . $string . $this->footer;
    }

    public function getKeyValueLine(string $key, string $value) : string
    {
        return "# $key $value".PHP_EOL;
    }
}
