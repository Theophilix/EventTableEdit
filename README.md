# EventTableEdit

Event Table Edit (abbrev. ETE) is a free and open source table plugin for Joomla 4.x and PHP 8.

With the plugin, you can create a **responsive, editable table** with CSV import and export function and XML export/import for table settings. It also has a full rights management (Joomla ACL). A small additional plugin (Content - Load ETE) allows users to display two or more tables in an article. It is installed automatically, but can be uninstalled without any consequences if this function is not desired.

With the plugin, you can also create a table to setup an **appointment booking system** with confirmation emails for users and the admin, including iCal calendar files for for users and the admin in the attachment. Moreover, you can use the booking system to create a volunteer table for an event, where volunteers can enter their names if they want to help for a certain time. As it is based on a CSS-template, the layout of the table can be changed easily. The responsive function is based on the "Column Toggle Table with Mini Map" from the tablesaw plugins (https://github.com/filamentgroup/tablesaw).

Try all the functions (including backend) on the demo site: https://demo.eventtableedit.com. Log in as user or admin with the given login credentials.

Download latest version 1.0.0 (release date: 01.03.2022) here: https://github.com/Theophilix/EventTableEdit/archive/refs/heads/main.zip.

Download older releases here: https://github.com/Theophilix/EventTableEdit/releases.

If needed, you can find ETE for Joomla 3.x as an archive here: https://github.com/Theophilix/event-table-edit-old

Version history: scroll down to "II".

## I Features:

- Editable table (insert pictures, BBCode thanks to https://github.com/milesj/decoda with option to blacklist offensive words, embed videos from different platforms etc ...)
- Sorting options (A-Z, Z-A, natural sorting is used)
- Choice of layout mode (stack, swipe, toggle) for enhanced responsiveness
- Instant filter / search
- Search and replace function (if admin is logged in)
- Multiple appointment booking function with confirmation email and ICAL calendar (.ics file) attachment, admin can edit cells (bookings) from frontend.
- Complete rights management (Joomla ACL: add/delete rows, edit cells, rearrange rows, administer table from frontend)
- Multilingual (currently available: DE, EN)
- CSV and TXT import with different formats (text, date, time, integer, float, boolean, four state, link, mail) 
  and import settings (separator, values in quotes or not)
- CSV Export
- XML import and export: import and export a table (normal or appointment) with all settings
- Own CSS based template
- A small additional plugin ("Content - Load ETE") allows users to display two or more tables in an article. It is installed automatically but can be uninstalled without any consequences if this function is not desired.

Frontend view options:
- Sort columns (setting in rights management)
- Delete rows (setting in rights management)
- Add rows (setting in rights management)
- Filter rows / Instant search
- Pagination
- Print view
- Administer table (setting in rights management) with quick csv export and import


Backend options:

a) General
- Normal or appointment booking function
- Options for appointment booking function:
  + ICAL / .ics-File options (location, subject, name of file)
  + Set admin email address and email display name
  + Confirmation email settings (chose subject and message text with appointment-date and -time-variables)
  + CSV Import and Export (quick csv export and import via admin login in frontend)
  + Show or hide user names to user or admin
  + Set timelimit for bookings
  + option to send two or more appoinment informations in one ics file
  + add global options, so admins can offer options (p. ex. different persons or services) and users can choose them from a list. If a user clicks on an option, the specific appointment table, that has been set in backend, is loaded.
- Show or hide table title
- Usertext before and after table
- Show or hide column to delete or sort rows
- Enable automatic column sorting when table is loaded
- Use Metadata
- Enhanced SEO
- Support for BB-Code (including emoticons and option to censor offensive words, more info: https://github.com/milesj/decoda)

b) Layout / Style

Choose or select:
- Date format
- Time format
- Float separator ("," or ".")
- Cell spacing
- Cell padding
- Colors of alternating rows
- Maximum length of cell content
- Display table in the same, or a new window
- Activate table scroll function, define height

Please post all feature requests in the issues tab.

## II Version history

**For version 1.0.0:**

Version numbers (compare old ETE here: https://github.com/Theophilix/event-table-edit-old) have been reset as massive changes had to be made for Joomla 8.x.



