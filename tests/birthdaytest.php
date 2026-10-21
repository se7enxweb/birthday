<?php
/**
 * DB-less tests of the birthday datatype. Reads the two classes with small stand-ins for the kernel classes they
 * touch, so no database and no kernel bootstrap is needed.
 *
 *   php tests/birthdaytest.php
 *
 *  BD-01  eZBirthday: validity, age, zodiac, empty object
 *  BD-02  the validation of the posted parts (required, optional, half filled, text, impossible dates)
 *  BD-03  what is stored: YYYY-MM-DD, null when empty
 *  BD-04  the templates carry no inline CSS or JS and read the default from data_text1
 */
class ezpI18n { static function tr( $c, $s ) { return $s; } }
class eZInputValidator { const STATE_ACCEPTED = 1; const STATE_INVALID = 2; }
class eZDataType
{
    function __construct( $s, $n, $a = array() ) {}
    static function register( $a, $b ) {}
}
class FakeHttp
{
    public $post = array();
    function hasPostVariable( $n ) { return array_key_exists( $n, $this->post ); }
    function postVariable( $n ) { return $this->post[$n]; }
}
class FakeClassAttribute
{
    public $required = false;
    function attribute( $n )
    {
        return $n === 'is_required' ? $this->required : false;
    }
}
class FakeAttribute
{
    public $data = array( 'id' => 7 );
    public $class;
    public $error = '';
    function __construct() { $this->class = new FakeClassAttribute(); }
    function attribute( $n ) { return isset( $this->data[$n] ) ? $this->data[$n] : null; }
    function setAttribute( $n, $v ) { $this->data[$n] = $v; }
    function contentClassAttribute() { return $this->class; }
    function setValidationError( $m ) { $this->error = $m; }
}

require __DIR__ . '/../datatypes/ezbirthday/ezbirthday.php';
require __DIR__ . '/../datatypes/ezbirthday/ezbirthdaytype.php';

$failures = 0;
function bdCheck( $ok, $message )
{
    global $failures;
    echo ( $ok ? 'PASS' : 'FAIL' ), ": $message\n";
    if ( !$ok )
        $failures++;
}

// BD-01
$b = new eZBirthday( array( 'year' => '1990', 'month' => '05', 'day' => '17' ) );
bdCheck( $b->attribute( 'is_valid' ) === true, 'BD-01 1990-05-17 is valid' );
bdCheck( $b->attribute( 'year' ) === 1990 && $b->attribute( 'month' ) === 5 && $b->attribute( 'day' ) === 17, 'BD-01 parts are integers' );
bdCheck( $b->attribute( 'zodiac_no' ) === '2' || $b->attribute( 'zodiac_no' ) === 2, 'BD-01 17 May is Taurus (2)' );
bdCheck( $b->attribute( 'zodiac_name' ) === 'Taurus', 'BD-01 zodiac name' );
bdCheck( is_int( $b->attribute( 'age' ) ) && $b->attribute( 'age' ) >= 35, 'BD-01 age is an integer' );
bdCheck( is_int( $b->attribute( 'days_on_earth' ) ) && $b->attribute( 'days_on_earth' ) > 12000, 'BD-01 days on earth is an integer' );
$s = eZBirthday::get_zodiac_sign( 1, 19 );
bdCheck( $s['Name'] === 'Capricorn', 'BD-01 19 January is Capricorn (month 1 before the 20th looks back)' );
$s = eZBirthday::get_zodiac_sign( 9, 20 );
bdCheck( $s['Name'] === 'Virgo', 'BD-01 20 September is Virgo, spelled correctly' );
bdCheck( eZBirthday::get_zodiac_sign( 13, 1 ) === array(), 'BD-01 month 13 gives no sign' );
$e = new eZBirthday();
bdCheck( $e->attribute( 'is_valid' ) === false && $e->attribute( 'year' ) === null, 'BD-01 an empty birthday is not valid and reads null' );
$bad = new eZBirthday( array( 'year' => '2023', 'month' => '02', 'day' => '30' ) );
bdCheck( $bad->attribute( 'is_valid' ) === false && $bad->attribute( 'age' ) === null, 'BD-01 30 February is not valid, no age' );
bdCheck( $e->attribute( 'nonsense' ) === null && $e->hasAttribute( 'age' ) && !$e->hasAttribute( 'nonsense' ), 'BD-01 unknown attributes' );

// BD-02
function bdValidate( $required, $parts, $collect = false )
{
    $type = new eZBirthdayType();
    $a = new FakeAttribute();
    $a->class->required = $required;
    $h = new FakeHttp();
    if ( $parts !== null )
        $h->post = array( 'B_birthday_year_7' => $parts[0], 'B_birthday_month_7' => $parts[1], 'B_birthday_day_7' => $parts[2] );
    $r = $collect ? $type->validateCollectionAttributeHTTPInput( $h, 'B', $a ) : $type->validateObjectAttributeHTTPInput( $h, 'B', $a );
    return array( $r, $a->error );
}
list( $r ) = bdValidate( false, array( '', '', '' ) );
bdCheck( $r === eZInputValidator::STATE_ACCEPTED, 'BD-02 optional and empty is accepted' );
list( $r ) = bdValidate( false, null );
bdCheck( $r === eZInputValidator::STATE_ACCEPTED, 'BD-02 optional and not posted is accepted' );
list( $r, $m ) = bdValidate( true, array( '', '', '' ) );
bdCheck( $r === eZInputValidator::STATE_INVALID && $m === 'Missing date input.', 'BD-02 required and empty is refused' );
list( $r, $m ) = bdValidate( false, array( '1990', '', '' ) );
bdCheck( $r === eZInputValidator::STATE_INVALID && $m === 'Missing date input.', 'BD-02 a half filled date is refused' );
list( $r ) = bdValidate( true, array( '1990', '5', '17' ) );
bdCheck( $r === eZInputValidator::STATE_ACCEPTED, 'BD-02 a real date is accepted' );
list( $r, $m ) = bdValidate( true, array( '1990', '2', '30' ) );
bdCheck( $r === eZInputValidator::STATE_INVALID && $m === 'Please enter a correct date.', 'BD-02 30 February is refused' );
list( $r, $m ) = bdValidate( true, array( 'abc', '2', '3' ) );
bdCheck( $r === eZInputValidator::STATE_INVALID && $m === 'Please enter a correct date.', 'BD-02 text is refused without a TypeError' );
list( $r ) = bdValidate( true, array( '19900', '2', '3' ) );
bdCheck( $r === eZInputValidator::STATE_INVALID, 'BD-02 a five digit year is refused' );
list( $r ) = bdValidate( true, array( '1990', '5', '17' ), true );
bdCheck( $r === eZInputValidator::STATE_ACCEPTED, 'BD-02 the collection validator accepts a real date' );

// BD-03
$type = new eZBirthdayType();
$a = new FakeAttribute();
$h = new FakeHttp();
$h->post = array( 'B_birthday_year_7' => '1990', 'B_birthday_month_7' => '5', 'B_birthday_day_7' => '7' );
$type->fetchObjectAttributeHTTPInput( $h, 'B', $a );
bdCheck( $a->attribute( 'data_text' ) === '1990-05-07', 'BD-03 stored as YYYY-MM-DD' );
$h->post = array( 'B_birthday_year_7' => '', 'B_birthday_month_7' => '5', 'B_birthday_day_7' => '7' );
$type->fetchObjectAttributeHTTPInput( $h, 'B', $a );
bdCheck( $a->attribute( 'data_text' ) === null, 'BD-03 a missing part stores null' );
$a->setAttribute( 'data_text', null );
bdCheck( $type->hasObjectAttributeContent( $a ) === false, 'BD-03 null data_text is no content (no strlen(null) deprecation)' );
$c = $type->objectAttributeContent( $a );
bdCheck( $c->attribute( 'is_valid' ) === false, 'BD-03 content of null is an empty birthday' );
$a->setAttribute( 'data_text', '1990-05-07' );
bdCheck( $type->objectAttributeContent( $a )->attribute( 'day' ) === 7, 'BD-03 content reads the stored value back' );

// BD-04
$root = dirname( __DIR__ ) . '/design';
$it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS ) );
$n = 0;
$inline = array();
foreach ( $it as $f )
{
    if ( substr( $f->getFilename(), -4 ) !== '.tpl' )
        continue;
    $n++;
    $t = preg_replace( '/\{\*.*?\*\}/s', '', file_get_contents( $f->getPathname() ) );
    if ( preg_match( '/<style|style\s*=|<script|\son[a-z]+\s*=/i', $t ) )
        $inline[] = $f->getPathname();
}
bdCheck( $n >= 7 && !$inline, "BD-04 $n templates carry no inline CSS or JS" . ( $inline ? ' (' . implode( ', ', $inline ) . ')' : '' ) );
foreach ( glob( $root . '/standard/templates/class/datatype/*/ezbirthday.tpl' ) as $f )
    bdCheck( strpos( file_get_contents( $f ), 'data_int1' ) === false, 'BD-04 ' . basename( dirname( $f ) ) . ' class template reads data_text1' );

exit( $failures ? 1 : 0 );
