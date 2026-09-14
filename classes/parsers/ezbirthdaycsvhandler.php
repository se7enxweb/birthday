<?php
/**
 * CSV export handler for the ezbirthday datatype.
 *
 * Registered in settings/csv.ini.append.php, which is xrowextract's csv.ini,
 * and built by xrowextract's parser interface.
 *
 * It used to extend BaseHandler, which is bccie's and declares
 * exportAttribute( &$attribute, $separationChar ). This is called by
 * xrowextract with one argument, so the declaration was incompatible with its
 * parent and php refused the class the moment anything autoloaded it - which
 * ended the request rather than losing one column of a csv.
 *
 * XrowBaseHandler is the right parent: its exportAttribute() takes the one
 * argument this is called with, its escape() takes the one argument this
 * passes, and it declares the separationChar and escape properties that
 * xrowextract sets on every handler it builds. bccie's BaseHandler declares
 * neither, so those two assignments were doing nothing either.
 *
 * The value comes from data_text and not from content(): ezbirthday's
 * objectAttributeContent() returns an eZBirthday object, and only the column
 * holds the plain YYYY-MM-DD that belongs in a csv cell.
 *
 * @copyright Copyright (C) 7x / Exponential Foundation. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2 (or any later version)
 * @package birthday
 */

class eZBirthdayCsvHandler extends XrowBaseHandler
{
    /**
     * The birthday as it goes into a csv cell.
     *
     * @param eZContentObjectAttribute $attribute
     * @return string, empty when the attribute has never been set
     */
    public function exportAttribute( &$attribute )
    {
        return $this->escape( (string) $attribute->attribute( 'data_text' ) );
    }
}

?>
