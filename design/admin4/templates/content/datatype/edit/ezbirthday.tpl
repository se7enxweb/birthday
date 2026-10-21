{* admin4: the three parts stand in one row (.a4-content-parts, content.css), each label above its select *}
{if is_set( $attribute_base )|not}{def $attribute_base='ContentObjectAttribute'}{/if}
{include uri='design:content/datatype/birthday_fields.tpl' attribute=$attribute attribute_base=$attribute_base birthday=$attribute.content row_class='block a4-content-parts'}
