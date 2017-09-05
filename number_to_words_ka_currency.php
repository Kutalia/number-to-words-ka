<?php

function number_to_words_ka_currency ($number, $currency_eng, $use_suffix = true, $use_currency = true)
{
    $conjunction    = 'და';
    $negative       = 'მინუს ';
    if ($currency_eng === 'GEL') {
        $currency   = ' ლარი';
        $fractionSuffix = ' თეთრი';
    }
    elseif ($currency_eng === 'USD') {
        $currency   = 'დოლარი';
        $fractionSuffix = ' ცენტი';
    }
    $decimal        = ' და '  ;
    $suffix         = 'ი';
    $dictionary     = array(
        0                   => 'ნულ',
        1                   => 'ერთ',
        2                   => 'ორ',
        3                   => 'სამ',
        4                   => 'ოთხ',
        5                   => 'ხუთ',
        6                   => 'ექვს',
        7                   => 'შვიდ',
        8                   => 'რვა',
        9                   => 'ცხრა',
        10                  => 'ათ',
        11                  => 'თერთმეტ',
        12                  => 'თორმეტ',
        13                  => 'ცამეტ',
        14                  => 'თოთხმეტ',
        15                  => 'თხუთმეტ',
        16                  => 'თექვსმეტ',
        17                  => 'ჩვიდმეტ',
        18                  => 'თვრამეტ',
        19                  => 'ცხრამეტ',
        20                  => 'ოც',
        40                  => 'ორმოც',
        60                  => 'სამოც',
        80                  => 'ოთხმოც',
        100                 => 'ას',
        1000                => 'ათას',
        1000000             => 'მილიონ',
        1000000000          => 'მილიარდ',
        1000000000000       => 'ტრილიონ',
        1000000000000000    => 'კვადრილიონ',
        1000000000000000000 => 'კვინტილიონ',
    );
    $space = ' ';
    if (!is_numeric($number))
    {
        return false;
    }
    if ($number > PHP_INT_MAX or $number < -PHP_INT_MAX)
    {
        // overflow
        trigger_error(
            'number_to_words_ka_currency only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
            E_USER_WARNING
        );
        return false;
    }
    if ($number < 0)
    {
        return $negative . number_to_words_ka_currency(abs($number), $currency_eng, true, false);
    }
    $string = $fraction = null;
    if (strpos($number, '.') !== false)
    {
        list($number, $fraction) = explode('.', $number);
        if (strlen($fraction) === 1) {
            $fraction .= '0';
        }
        if (strlen($fraction) > 2) {
            trigger_error(
                'number_to_words_ka_currency only accepts numbers with fraction represented with maximum of two ciphers',
                E_USER_WARNING
            );
        }
    }
    switch (true)
    {
        case $number == 0:
            $string = $dictionary[0];
            break;
        case $number < 21:
            $string = $dictionary[(int)$number];
            break;
        case $number < 100:
            $twenties = ((int) ($number / 20)) * 20;
            $units  = $number % 20;
            $string = $dictionary[$twenties];
            if ($units)
            {
                $string .= $conjunction . $dictionary[$units];
            }
            break;
        case $number < 1000:
            $hundreds  = $number / 100;
            $remainder = $number % 100;
            $hundredsStr = $hundreds < 2 ? '' : $dictionary[$hundreds];
            $string = $hundredsStr . $dictionary[100];
            if ($remainder)
            {
                $string .= $space . number_to_words_ka_currency($remainder, $currency_eng, false, false);
            }
            break;
        default:
            $baseUnit = 1000 ** floor(log($number, 1000));
            $numBaseUnits = (int) ($number / $baseUnit);
            $remainder = $number % $baseUnit;
            if ($numBaseUnits < 2)
            {
                $string = $dictionary[$baseUnit];
            }
            else
            {
                $string = number_to_words_ka_currency($numBaseUnits, $currency_eng, true, false);
                $string .= $space . $dictionary[$baseUnit];
            }
            if ($remainder)
            {
                $string .= $space . number_to_words_ka_currency($remainder, $currency_eng, false, false);
            }
            else
            {
                $string .= $currency;
            }
            break;
    }
    // no suffix for 8 and 9
    if ($use_suffix and !in_array($number % 20, array(8, 9)))
    {
        $string .= $suffix;
    }
    if (null !== $fraction and is_numeric($fraction))
    {
        $string .= $currency;
        $string .= $decimal;
        $string .= number_to_words_ka_currency($fraction, $currency_eng, true, false);
        $string .= $fractionSuffix;
    }
    elseif ($use_currency === true)
    {
        $string .= $currency;
    }
    return $string;
}