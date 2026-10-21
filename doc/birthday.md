# birthday: the ezbirthday datatype

The extension adds the datatype `ezbirthday` (display name "Birthday") to Exponential. It stores a date of birth
as three drop-downs (year, month, day) and exposes the age, the zodiac sign and the days lived to templates.

## What it stores

| Where | Value |
|---|---|
| `ezcontentobject_attribute.data_text` | `YYYY-MM-DD` (for example `1990-05-17`), or empty when never set |
| sort key (`sort_key_string`) | the same string, so sorting and `>=` filters work as text |
| search metadata | the same string |
| `ezcontentclass_attribute.data_text1` | the class default: `0` empty, `1` current date |
| information collection | the collected value is stored in `data_text` of the collection attribute |

The year is stored with four digits. A date is only stored when year, month and day are all given.

The default of a class attribute lives in `data_text1`, not in `data_int1`. Earlier releases already stored it
there; releases up to 1.3.5 read it back from `data_int1` in the class edit and view templates, so the class
editor always showed "Empty". That is fixed in 1.3.6 and no data has to be migrated.

## Validation

| Input | Result |
|---|---|
| all three parts empty, attribute not required | accepted, nothing stored |
| all three parts empty, attribute required | "Missing date input." |
| one or two parts given | "Missing date input." (a half filled date is not a birthday) |
| text, a five digit year, or an impossible date such as 30 February | "Please enter a correct date." |
| a real date | accepted |

The messages are in the context `kernel/classes/datatypes`. An information collector attribute is not validated on
the object edit form; it is validated when the visitor sends the collection.

## Settings

The extension ships no settings of its own to change. Its INI files only register it:

| File | Setting | Purpose |
|---|---|---|
| `content.ini.append` | `[DataTypeSettings] ExtensionDirectories[]=birthday`, `AvailableDataTypes[]=ezbirthday` | makes the datatype known |
| `design.ini.append.php` | `[ExtensionSettings] DesignExtensions[]=birthday` | loads the templates |
| `site.ini.append.php` | `[RegionalSettings] TranslationExtensions[]=birthday` | loads the translations |
| `csv.ini.append.php` | `[General] ExportableDatatypes[]=ezbirthday` and `[ezbirthday] HandlerFile`, `HandlerClass` | the csv export of [xrowextract](https://github.com/se7enxweb/xrowextract), optional |

Activate it with `ActiveExtensions[]=birthday` (or `ActiveAccessExtensions[]=birthday`).

## Template usage

In an object view template, `$node.data_map.<identifier>.content` is an `eZBirthday` object:

| Attribute | Type | Meaning |
|---|---|---|
| `year`, `month`, `day` | integer | the parts; null when the attribute is empty |
| `is_valid`, `has_content` | boolean | true for a real calendar date |
| `age` | integer | completed years today |
| `days_on_earth` | integer | days between the date and today |
| `zodiac_no`, `zodiac_nr` | integer | 1 Aries to 12 Pisces |
| `zodiac_name` | string | the translated name of the sign |

```
{def $b=$node.data_map.birthday.content}
{if $b.is_valid}
    {$b.day}.{$b.month}.{$b.year}, {$b.age} years, {$b.zodiac_name}
{/if}
```

Use `$attribute.data_text` for the raw `YYYY-MM-DD`. The default view template prints the date in a `<time>`
element with `datetime` set to that value, with the month name localized by `datetime( 'custom', '%F' )`.

Templates shipped (design/standard unless noted):

- `content/datatype/edit/ezbirthday.tpl` and `collect/ezbirthday.tpl` include `content/datatype/birthday_fields.tpl`,
  three selects with `label for` and ids; the year list runs from the current year back 100 years and keeps a stored
  year outside that range selectable.
- `design/admin4/.../edit/ezbirthday.tpl` puts the three fields in one row with the shared `.a4-content-parts`
  class; no CSS of its own, so light and dark mode come from the admin4 tokens.
- `view/ezbirthday.tpl`, `result/info/ezbirthday.tpl`, `class/datatype/edit|view/ezbirthday.tpl`.

No template carries `<style>`, `style=""` or scripts.

## Fetch functions and operators

None. Use the generic `fetch( 'content', 'list', ... )` with an attribute filter on the class attribute, for example
`hash( 'attribute_filter', array( array( 'my_class/birthday', '>=', '1990-01-01' ) ) )`; the text sort key makes
that work.

## Translations

`translations/<locale>/translation.ts` in twelve languages, contexts `design/standard/class/datatype`,
`design/standard/content/datatype` and `kernel/classes/datatypes`. The zodiac "Virgo" keeps the historical message
id "Virgio"; a language without a translation for it now shows the correct English "Virgo".

## Tests

`php tests/birthdaytest.php` needs no database and no kernel: it loads the two classes with small stand-ins and
checks the value object, the validation, what is stored and the templates (no inline CSS or JS, class default read
from `data_text1`). Exit code 0 means every check passed.

## Upgrade notes

### 1.3.6

- PHP 8.0 to 8.5 clean: no null passed to `strlen()` or `preg_match()`, `checkdate()` is never called with text
  (it throws a `TypeError` since PHP 8), `eZBirthday::get_zodiac_sign()` and `gregorianToJD()` are static.
- `age` and `days_on_earth` are integers (they were floats).
- The year and day of an `eZBirthday` are integers; compare with `eq`, not as strings with leading zeros.
- Fixed: the information collection form never preselected the collected date; it had a stray `<option>` outside
  its select; the class edit form always showed "Empty"; a required attribute with only the first part empty was
  validated by operator precedence instead of by rule; an unserialized class attribute without
  `default-value` raised a warning.
- Fixed: "Virgio" shown for Virgo in English.
- Nothing to migrate. Clear the template-override cache (`php bin/php/ezcache.php --clear-all`) so the new
  `birthday_fields.tpl` and the admin4 template are found.

### 1.3.5 and earlier

See the release notes on GitHub.
