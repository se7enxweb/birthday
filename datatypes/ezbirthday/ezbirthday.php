<?php

class eZBirthday
{
    public function __construct( $birthday = array() )
    {
        if ( is_array( $birthday ) and count( $birthday ) > 0 )
        {
            $this->Day = (int) $birthday["day"];
            $this->Month = (int) $birthday["month"];
            $this->Year = (int) $birthday["year"];

            if ( checkdate( $this->Month, $this->Day, $this->Year ) !== false )
            {
                $zodiac = self::get_zodiac_sign( $this->Month, $this->Day );

                if ( is_array( $zodiac ) and count( $zodiac ) > 0 )
                {
                    $this->Zodiac_Nr = $zodiac["Number"];
                    $this->Zodiac_No = $this->Zodiac_Nr;
                    $this->Zodiac_Name = $zodiac["Name"];
                }

                $this->Days_On_Earth = (int) abs( self::gregorianToJD( $this->Month,
                                                                       $this->Day,
                                                                       $this->Year ) -
                                                                       self::gregorianToJD(
                                                                         (int) date( "m" ),
                                                                         (int) date( "d" ),
                                                                         (int) date( "Y" ) ) );

                $today = (int) ( date( "Y" ) . date( "m" ) . date( "d" ) );
                $birthday = (int) ( sprintf( "%04d", $this->Year ) . sprintf( "%02d", $this->Month ) . sprintf( "%02d", $this->Day ) );

                $this->Age = (int) floor( ( $today - $birthday ) / 10000 );
            }
        }
    }

    public static function gregorianToJD( $month, $day, $year )
    {
        if ( $month > 2 )
            $month = $month - 3;
        else
        {
            $month = $month + 9;
            $year = $year - 1;
        }

        $c = floor( $year / 100 );
        $ya = $year - ( 100 * $c );
        $j = floor( ( 146097 * $c ) / 4 );
        $j += floor( ( 1461 * $ya ) / 4 );
        $j += floor( ( ( 153 * $month ) + 2 ) / 5 );
        $j += $day + 1721119;

        return $j;
    }

    /**
     * "Virgo". The message id of the translation files has always been the misspelled "Virgio", so that one is
     * looked up first; a language that does not translate it gets the correct English name.
     */
    protected static function virgoName()
    {
        $translated = ezpI18n::tr( 'kernel/classes/datatypes', 'Virgio' );
        return $translated === 'Virgio' ? 'Virgo' : $translated;
    }

    public function attributes()
    {
        return array( 'day',
                      'month',
                      'year',
                      'is_valid',
                      'zodiac_nr',
                      'zodiac_no',
                      'zodiac_name',
                      'days_on_earth',
                      'has_content',
                      'age' );
    }

    public function hasAttribute( $name )
    {
        $attributes = self::attributes();
        if ( in_array( $name, $attributes ) )
            return true;
        else
            return false;
    }

    public static function get_zodiac_sign( $month, $day )
    {
        // Check arguments for validity
        if ( $month < 1 || $month > 12 || $day < 1 || $day > 31 )
            return array();

        // List of zodiac signs with start day (start month is array-index + 1)
        $signs = array(
                   array( "20" =>  "11" ),
                   array( "19" =>  "12" ),
                   array( "21" =>  "1" ),
                   array( "20" =>  "2" ),
                   array( "21" =>  "3" ),
                   array( "22" =>  "4" ),
                   array( "23" =>  "5" ),
                   array( "23" =>  "6" ),
                   array( "23" =>  "7" ),
                   array( "24" =>  "8" ),
                   array( "22" =>  "9" ),
                   array( "22" =>  "10" )
                   );

        $name = array(  1 =>  ezpI18n::tr( 'kernel/classes/datatypes', 'Aries' ),
                        2 =>  ezpI18n::tr( 'kernel/classes/datatypes', 'Taurus' ),
                        3 =>  ezpI18n::tr( 'kernel/classes/datatypes', 'Gemini' ),
                        4 =>  ezpI18n::tr( 'kernel/classes/datatypes', 'Cancer' ),
                        5 =>  ezpI18n::tr( 'kernel/classes/datatypes', 'Leo' ),
                        6 =>  self::virgoName(),
                        7 =>  ezpI18n::tr( 'kernel/classes/datatypes', 'Libra' ),
                        8 =>  ezpI18n::tr( 'kernel/classes/datatypes', 'Scorpio' ),
                        9 =>  ezpI18n::tr( 'kernel/classes/datatypes', 'Sagittarius' ),
                        10 => ezpI18n::tr( 'kernel/classes/datatypes', 'Capricorn' ),
                        11 => ezpI18n::tr( 'kernel/classes/datatypes', 'Aquarius' ),
                        12 => ezpI18n::tr( 'kernel/classes/datatypes', 'Pisces' ),
                    );

        // each() is gone since PHP 8.0: the first key and value of the one-entry array
        $sign = $signs[(int)$month-1];
        $sign_start = key( $sign );
        $sign_name = current( $sign );
        if ( $day < $sign_start )
        {
            $sign = $signs[( $month -2 < 0 ) ? $month = 11: $month -= 2];
            $sign_start = key( $sign );
            $sign_name = current( $sign );
        }
        return array( "Number" => $sign_name, "Name" => $name[$sign_name] );
    }

    public function attribute( $name )
    {
        switch ( $name )
        {
            case "day":
            {
                return $this->Day;
            }break;

            case "month":
            {
                return $this->Month;
            }break;

            case "year":
            {
                return $this->Year;
            }break;

            case "has_content":
            case "is_valid":
            {
                return checkdate( (int) $this->Month, (int) $this->Day, (int) $this->Year );
            }break;

            case "zodiac_no":
            case "zodiac_nr":
            {
                return $this->Zodiac_No;
            }break;

            case "zodiac_name":
            {
                return $this->Zodiac_Name;
            }break;

            case "days_on_earth" :
            {
                return $this->Days_On_Earth;
            }break;

            case "age" :
            {
                return $this->Age;
            }break;
        }
        return null;
    }

    private $Day;
    private $Month;
    private $Year;
    private $Zodiac_Nr;
    private $Zodiac_No;
    private $Zodiac_Name;
    private $Days_On_Earth;
    private $Age;
}
?>
