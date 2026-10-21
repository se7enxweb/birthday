{*
    $attribute.content.year - year
    $attribute.content.month - month
    $attribute.content.day - day
    $attribute.content.zodiac_name - the name of the zodiac sign
    $attribute.content.zodiac_no - the number of the zodiac sign (1 Aries ... 12 Pisces)
    $attribute.content.is_valid - true, if the date is correct
    $attribute.content.days_on_earth - number of days from the date to today
    $attribute.content.age - the age in years
*}
{if $attribute.content.is_valid}
    <time datetime="{$attribute.data_text|wash}">{makedate( $attribute.content.month, $attribute.content.day, 1990 )|datetime( 'custom', '%d %F' )} {$attribute.content.year}</time>
{/if}
