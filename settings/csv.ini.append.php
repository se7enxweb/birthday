<?php /* #?ini charset="utf-8"?

[General]
ExportableDatatypes[]=ezbirthday

[ezbirthday]
# xrowextract reads this path and calls file_exists() on it before it will
# build the handler, and an ini cannot ask eZExtension::extensionPath() where
# this extension actually is. So the path is written the way it has always
# been, and this extension has to be installed in extension/ for the csv
# handler to be found. Installed under another extension root - see
# site.ini [ExtensionSettings] AdditionalExtensionDirectories[] - everything
# else in here still works and only the csv export is quietly skipped.
HandlerFile=extension/birthday/classes/parsers/ezbirthdaycsvhandler.php

# The class extends XrowBaseHandler, so xrowextract has to be installed. It is
# the only thing in this extension that needs it, and nothing else here is
# affected if it is not.
HandlerClass=eZBirthdayCsvHandler

*/ ?>
