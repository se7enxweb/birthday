{*
    The three selects of a birthday, shared by the edit and the collect template.

    Parameters:
      $attribute       the content object attribute
      $attribute_base  the post variable prefix (ContentObjectAttribute by default)
      $birthday        the eZBirthday object to preselect (the attribute content or the collected content)
      $row_class       class of the wrapper: 'block' for the legacy designs, 'block a4-content-parts' in admin4
*}
{def $byear=currentdate()|datetime( 'custom', '%Y' )
     $id_prefix=concat( 'ezbirthday-', $attribute_base, '-', $attribute.id )
     $label_year='Year'|i18n( 'design/standard/content/datatype' )
     $label_month='Month'|i18n( 'design/standard/content/datatype' )
     $label_day='Day'|i18n( 'design/standard/content/datatype' )}
<div class="{$row_class|wash}" role="group" aria-label="{$attribute.contentclass_attribute_name|wash}">
    <div class="element">
        <label for="{$id_prefix}-year">{$label_year}</label>
        <select id="{$id_prefix}-year" name="{$attribute_base}_birthday_year_{$attribute.id}" title="{'Please enter the year'|i18n( 'design/standard/content/datatype' )}">
            <option value=""></option>
        {if and( $birthday.is_valid, or( gt( $birthday.year, $byear ), lt( $birthday.year, sub( $byear, 100 ) ) ) )}
            <option value="{$birthday.year}" selected="selected">{$birthday.year}</option>
        {/if}
        {for $byear to sub( $byear, 100 ) as $i}
            <option value="{$i}"{if eq( $birthday.year, $i )} selected="selected"{/if}>{$i}</option>
        {/for}
        </select>
    </div>

    <div class="element">
        <label for="{$id_prefix}-month">{$label_month}</label>
        <select id="{$id_prefix}-month" name="{$attribute_base}_birthday_month_{$attribute.id}" title="{'Please enter the month'|i18n( 'design/standard/content/datatype' )}">
            <option value=""></option>
        {for 1 to 12 as $i}
            <option value="{$i}"{if eq( $birthday.month, $i )} selected="selected"{/if}>{$i}</option>
        {/for}
        </select>
    </div>

    <div class="element">
        <label for="{$id_prefix}-day">{$label_day}</label>
        <select id="{$id_prefix}-day" name="{$attribute_base}_birthday_day_{$attribute.id}" title="{'Please enter the day'|i18n( 'design/standard/content/datatype' )}">
            <option value=""></option>
        {for 1 to 31 as $i}
            <option value="{$i}"{if eq( $birthday.day, $i )} selected="selected"{/if}>{$i}</option>
        {/for}
        </select>
    </div>
</div>
<div class="break"></div>
