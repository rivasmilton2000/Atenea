<?php

class DteMoneyToWords
{
    public static function toUsd(float $amount): string
    {
        $amount = round($amount, 2);
        $integerPart = (int) floor($amount);
        $decimalPart = (int) round(($amount - $integerPart) * 100);

        $words = self::convertNumber($integerPart);
        $result = trim($words . ' DOLARES CON ' . sprintf('%02d/100', $decimalPart) . ' USD');

        return preg_replace('/\s+/', ' ', $result) ?: 'CERO DOLARES CON 00/100 USD';
    }

    private static function convertNumber(int $number): string
    {
        if ($number === 0) {
            return 'CERO';
        }

        if ($number < 0) {
            return 'MENOS ' . self::convertNumber(abs($number));
        }

        if ($number < 1000) {
            return self::convertHundreds($number);
        }

        if ($number < 1000000) {
            $thousands = (int) floor($number / 1000);
            $remainder = $number % 1000;
            $prefix = $thousands === 1 ? 'MIL' : self::convertHundreds($thousands) . ' MIL';

            return trim($prefix . ' ' . ($remainder > 0 ? self::convertHundreds($remainder) : ''));
        }

        if ($number < 1000000000000) {
            $millions = (int) floor($number / 1000000);
            $remainder = $number % 1000000;
            $prefix = $millions === 1 ? 'UN MILLON' : self::convertNumber($millions) . ' MILLONES';

            return trim($prefix . ' ' . ($remainder > 0 ? self::convertNumber($remainder) : ''));
        }

        return (string) $number;
    }

    private static function convertHundreds(int $number): string
    {
        $units = [
            0 => '',
            1 => 'UNO',
            2 => 'DOS',
            3 => 'TRES',
            4 => 'CUATRO',
            5 => 'CINCO',
            6 => 'SEIS',
            7 => 'SIETE',
            8 => 'OCHO',
            9 => 'NUEVE',
            10 => 'DIEZ',
            11 => 'ONCE',
            12 => 'DOCE',
            13 => 'TRECE',
            14 => 'CATORCE',
            15 => 'QUINCE',
            16 => 'DIECISEIS',
            17 => 'DIECISIETE',
            18 => 'DIECIOCHO',
            19 => 'DIECINUEVE',
            20 => 'VEINTE',
            21 => 'VEINTIUNO',
            22 => 'VEINTIDOS',
            23 => 'VEINTITRES',
            24 => 'VEINTICUATRO',
            25 => 'VEINTICINCO',
            26 => 'VEINTISEIS',
            27 => 'VEINTISIETE',
            28 => 'VEINTIOCHO',
            29 => 'VEINTINUEVE',
        ];

        $tens = [
            30 => 'TREINTA',
            40 => 'CUARENTA',
            50 => 'CINCUENTA',
            60 => 'SESENTA',
            70 => 'SETENTA',
            80 => 'OCHENTA',
            90 => 'NOVENTA',
        ];

        $hundreds = [
            100 => 'CIEN',
            200 => 'DOSCIENTOS',
            300 => 'TRESCIENTOS',
            400 => 'CUATROCIENTOS',
            500 => 'QUINIENTOS',
            600 => 'SEISCIENTOS',
            700 => 'SETECIENTOS',
            800 => 'OCHOCIENTOS',
            900 => 'NOVECIENTOS',
        ];

        if ($number < 30) {
            return $units[$number];
        }

        if ($number < 100) {
            $ten = (int) floor($number / 10) * 10;
            $unit = $number % 10;

            return $unit === 0 ? $tens[$ten] : $tens[$ten] . ' Y ' . $units[$unit];
        }

        if ($number === 100) {
            return 'CIEN';
        }

        if ($number < 200) {
            return 'CIENTO ' . self::convertHundreds($number - 100);
        }

        $hundred = (int) floor($number / 100) * 100;
        $remainder = $number % 100;

        return trim($hundreds[$hundred] . ' ' . ($remainder > 0 ? self::convertHundreds($remainder) : ''));
    }
}
