<?php

namespace rohsyl\LaravelOtc\Generators;

final class NumberGenerator implements GeneratorContract
{
    public function generate(): string
    {
        $number = random_int(
            min: 00_000,
            max: 99_999,
        );

        return str_pad(
            string: strval($number),
            length: 5,
            pad_string: '0',
            pad_type: STR_PAD_LEFT,
        );
    }
}
