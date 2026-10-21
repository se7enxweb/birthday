{* The default is kept in data_text1 of the class attribute, as it always was *}
<div class="block">
<label for="ezbirthday-default-{$class_attribute.id}">{'Default value'|i18n( 'design/standard/class/datatype' )}</label>
<select id="ezbirthday-default-{$class_attribute.id}" name="ContentClass_ezbirthday_default_{$class_attribute.id}">
    <option value="0"{if eq( $class_attribute.data_text1, 0 )} selected="selected"{/if}>{'Empty'|i18n( 'design/standard/class/datatype' )}</option>
    <option value="1"{if eq( $class_attribute.data_text1, 1 )} selected="selected"{/if}>{'Current date'|i18n( 'design/standard/class/datatype' )}</option>
</select>
</div>
