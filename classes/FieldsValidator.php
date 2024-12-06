<?php

namespace APP\plugins\generic\scieloTranslationsFields\classes;

class FieldsValidator
{
    public function validateDoi(string $doi): bool
    {
        return (preg_match('/^10\.\d{4,9}\/[-._;()\/:A-Z0-9]+$/i', $doi) === 1);
    }
}
