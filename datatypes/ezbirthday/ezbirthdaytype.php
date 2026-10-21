<?php

class eZBirthdayType extends eZDataType
{
    const DATA_TYPE_STRING = 'ezbirthday';
    const BIRTHDAY_DEFAULT = 'data_text1';
    const BIRTHDAY_DEFAULT_EMTPY = 0;
    const BIRTHDAY_DEFAULT_CURRENT_DATE = 1;

    function __construct()
    {
        parent::__construct( self::DATA_TYPE_STRING, ezpI18n::tr( 'kernel/classes/datatypes', "Birthday", 'Datatype name' ),
                           array( 'serialize_supported' => true ) );

    }

    public static function addZero( $value )
    {
        return sprintf( "%02d", $value );
    }


    /**
     * The three posted parts of a birthday for one attribute.
     *
     * @return array|null array( year, month, day ) as trimmed strings, null when the form did not send all three
     */
    protected static function postedParts( $http, $base, $contentObjectAttribute )
    {
        $id = $contentObjectAttribute->attribute( 'id' );
        $names = array( $base . '_birthday_year_' . $id, $base . '_birthday_month_' . $id, $base . '_birthday_day_' . $id );
        foreach ( $names as $name )
        {
            if ( !$http->hasPostVariable( $name ) )
                return null;
        }
        return array( trim( (string) $http->postVariable( $names[0] ) ),
                      trim( (string) $http->postVariable( $names[1] ) ),
                      trim( (string) $http->postVariable( $names[2] ) ) );
    }

    /**
     * True when the three parts are whole numbers that form a real calendar date. checkdate() throws a TypeError
     * on text since PHP 8, so the text is rejected here first.
     */
    protected static function isRealDate( $year, $month, $day )
    {
        if ( !ctype_digit( (string) $year ) or !ctype_digit( (string) $month ) or !ctype_digit( (string) $day ) )
            return false;
        if ( strlen( (string) $year ) > 4 )
            return false;
        return checkdate( (int) $month, (int) $day, (int) $year );
    }

    /**
     * The value stored in data_text: YYYY-MM-DD, or null when a part is missing.
     */
    protected static function storedValue( $year, $month, $day )
    {
        if ( $year === '' or $month === '' or $day === '' )
            return null;
        return sprintf( '%04d', (int) $year ) . '-' . self::addZero( (int) $month ) . '-' . self::addZero( (int) $day );
    }

    /**
     * Shared by both validate methods; $skipWhenCollector is true for object attributes that collect information.
     */
    protected function validateParts( $http, $base, $contentObjectAttribute, $skipWhenCollector )
    {
        $parts = self::postedParts( $http, $base, $contentObjectAttribute );
        list( $year, $month, $day ) = $parts === null ? array( '', '', '' ) : $parts;

        $classAttribute = $contentObjectAttribute->contentClassAttribute();
        $isRequired = (bool) $classAttribute->attribute( 'is_required' );

        if ( $skipWhenCollector and $classAttribute->attribute( 'is_information_collector' ) )
            return eZInputValidator::STATE_ACCEPTED;

        if ( !$isRequired and $year === '' and $month === '' and $day === '' )
            return eZInputValidator::STATE_ACCEPTED;

        // required: all three parts; optional: all or none (a half filled date is not a birthday)
        if ( $year === '' or $month === '' or $day === '' )
        {
            $contentObjectAttribute->setValidationError( ezpI18n::tr( 'kernel/classes/datatypes',
                                                                 'Missing date input.' ) );
            return eZInputValidator::STATE_INVALID;
        }

        if ( self::isRealDate( $year, $month, $day ) )
            return eZInputValidator::STATE_ACCEPTED;

        $contentObjectAttribute->setValidationError( ezpI18n::tr( 'kernel/classes/datatypes',
                                                             'Please enter a correct date.' ) );
        return eZInputValidator::STATE_INVALID;
    }

    /*!
     Validates the input and returns true if the input was
     valid for this datatype.
    */
    function validateObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        return $this->validateParts( $http, $base, $contentObjectAttribute, true );
    }

    /*!
     Validates the InformationCollection input and returns true if the input was
     valid for this datatype.
    */
    function validateCollectionAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        return $this->validateParts( $http, $base, $contentObjectAttribute, false );
    }

    /*!
     Fetches the http post var integer input and stores it in the data instance.
    */
    function fetchObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        $parts = self::postedParts( $http, $base, $contentObjectAttribute );
        list( $year, $month, $day ) = $parts === null ? array( '', '', '' ) : $parts;
        $contentObjectAttribute->setAttribute( "data_text", self::storedValue( $year, $month, $day ) );
        return true;
    }

    /*!
     Fetches the http post variables for collected information
    */
    function fetchCollectionAttributeHTTPInput( $collection, $collectionAttribute, $http, $base, $contentObjectAttribute )
    {
        $parts = self::postedParts( $http, $base, $contentObjectAttribute );
        if ( $parts === null )
            return false;
        list( $year, $month, $day ) = $parts;
        $collectionAttribute->setAttribute( 'data_text', self::storedValue( $year, $month, $day ) );
        return true;
    }

    /*!
     Returns the content.
    */
    function objectAttributeContent( $contentObjectAttribute )
    {
        $dateStr = (string) $contentObjectAttribute->attribute( 'data_text' );
        if ( preg_match( "/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $dateStr, $valueArray ) )
        {
            $year = $valueArray[1];
            $month =  $valueArray[2];
            $day = $valueArray[3] ;
            $birthday = new eZBirthday( array ("year" => $year, "month" => $month, "day" => $day ) );
        }
        else
            $birthday = new eZBirthday();
        return $birthday;
    }

    /*!
     Set class attribute value for template version
    */
    function initializeClassAttribute( $classAttribute )
    {
        if ( $classAttribute->attribute( self::BIRTHDAY_DEFAULT ) == null )
            $classAttribute->setAttribute( self::BIRTHDAY_DEFAULT, 0 );
        $classAttribute->store();
    }

    /*!
     Sets the default value.
    */
    function initializeObjectAttribute( $contentObjectAttribute, $currentVersion, $originalContentObjectAttribute )
    {
        if ( $currentVersion != false )
        {
            $dataText = $originalContentObjectAttribute->attribute( "data_text" );
            $contentObjectAttribute->setAttribute( "data_text", $dataText );
        }
        else
        {
            $contentClassAttribute = $contentObjectAttribute->contentClassAttribute();
            $defaultType = $contentClassAttribute->attribute( self::BIRTHDAY_DEFAULT );
            if ( $defaultType == 1 )
            {
                $day = eZBirthdayType::addZero( date( 'd' ) );
                $month = eZBirthdayType::addZero( date( 'm' ) );
                $year = date('Y');
                $contentObjectAttribute->setAttribute( "data_text", $year . "-" . $month . "-" . $day );
            }
        }
    }

    function fetchClassAttributeHTTPInput( $http, $base, $classAttribute )
    {
        $default = $base . "_ezbirthday_default_" . $classAttribute->attribute( 'id' );
        if ( $http->hasPostVariable( $default ) )
        {
            $defaultValue = (int) $http->postVariable( $default );
            if ( $defaultValue !== self::BIRTHDAY_DEFAULT_CURRENT_DATE )
                $defaultValue = self::BIRTHDAY_DEFAULT_EMTPY;
            $classAttribute->setAttribute( self::BIRTHDAY_DEFAULT,  $defaultValue );
        }
        return true;
    }

    /*!
     Returns the meta data used for storing search indeces.
    */
    function metaData( $contentObjectAttribute )
    {
        return $contentObjectAttribute->attribute( 'data_text' );
    }

    /*!
     \return string representation of an contentobjectattribute data for simplified export
     */
    function toString( $contentObjectAttribute )
    {
        return $contentObjectAttribute->attribute( 'data_text' );
    }

    function fromString( $contentObjectAttribute, $string )
    {
        return $contentObjectAttribute->setAttribute( 'data_text', $string );
    }

    /*!
     Returns the date.
    */
    function title( $objectAttribute, $name = null )
    {
        return $objectAttribute->attribute( "data_text" );
    }

    function hasObjectAttributeContent( $contentObjectAttribute )
    {
        return ( strlen( (string) $contentObjectAttribute->attribute( "data_text" ) ) > 0 );
    }

    /*!
     \reimp
    */
    function sortKey( $contentObjectAttribute )
    {
        return $contentObjectAttribute->attribute( 'data_text' );
    }

    function sortKeyType()
    {
        return 'string';
    }

    /*!
     \reimp
    */
    function serializeContentClassAttribute( $classAttribute, $attributeNode, $attributeParametersNode )
    {
        $dom = $attributeParametersNode->ownerDocument;
        $defaultValue = $classAttribute->attribute( self::BIRTHDAY_DEFAULT );
        $element = $dom->createElement( 'default-value' );
        switch ( $defaultValue )
        {
            case self::BIRTHDAY_DEFAULT_EMTPY:
            {
                $element->setAttribute( 'type', 'empty' );
                $attributeParametersNode->appendChild( $element );
            } break;
            case self::BIRTHDAY_DEFAULT_CURRENT_DATE:
            {
                $element->setAttribute( 'type', 'current-date' );
                $attributeParametersNode->appendChild( $element );
                }
                break;
        }
    }

    function unserializeContentClassAttribute( $classAttribute, $attributeNode, $attributeParametersNode )
    {
        
        $defaultValue = 'empty';
        foreach ( $attributeParametersNode->getElementsByTagName( 'default-value' ) as $attribute )
        {
            $defaultValue = $attribute->getAttribute( 'type' );
        }
        
        switch ( $defaultValue )
        {
            case 'empty':
                {
                    $classAttribute->setAttribute( self::BIRTHDAY_DEFAULT, self::BIRTHDAY_DEFAULT_EMTPY );
                }
                break;
            case 'current-date':
                {
                    $classAttribute->setAttribute( self::BIRTHDAY_DEFAULT, self::BIRTHDAY_DEFAULT_CURRENT_DATE );
                }
                break;
        }
    }

    /*!
     \return the collect information action if enabled
    */
    function contentActionList( $classAttribute )
    {
        if ( $classAttribute->attribute( 'is_information_collector' ) == true )
        {
            return array( array( 'name' => 'Send',
                                 'action' => 'ActionCollectInformation'
                                 ) );
        }
        else
            return array();
    }

    function isIndexable()
    {
        return true;
    }

    /*!
     \reimp
    */
    function isInformationCollector()
    {
        return true;
    }

}

eZDataType::register( eZBirthdayType::DATA_TYPE_STRING, "ezbirthdaytype" );

?>
