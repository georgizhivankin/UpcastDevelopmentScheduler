# About

This small script can be executed from a command line shell or a web browser to export the mid month developer meeting dates and the monthly testing dates for the next 6 months into a .csv file.

# Requirements

This script requires PHP V 5.3 or later to run correctly as some DateTime class methods were enabled in PHP 5.2 and 5.3. The script does not use additional external libraries, therefore, none are included.

# How to Run

In order to run this script, you need to put it into a directory of its own and go to that directory within a web browser or through your favourite shell programme and execute the script with the following command:
```php
PHP -F UpcastDevelopmentScheduler.php
```
The script can run without requiring any additional configuration or setup, but if you wish to change the period, the file name of the export or the number of months that the export should span, you can do it through the following arguments that the script accepts. Below is an example of the arguments in use, feel free to modify them to your likings:
```php
php -f UpcastDevelopmentScheduler.php 2014-05-01 file_for_2014.csv 12
```
- The first argument `2014-05-01` is a date value in the (YYYY/mm/dd) format and you should always provide it as such.
- The second argument `file_for_2014.csv` is the file name of the file where the export would be saved to. You can use any combination of [A-Z][0-9] characters for the file name, but make sure to finish it up in .csv in order to make sure that you would be able to open it afterwards. If you do not provide a file name, the default one is `YYmmdd_Upcast_Monthly_Schedule.csv`
- The final argument `12` allows you to choose how many months the export should cover, based on the given month in the first argument. For example, if you have written `2013-01-01` as a first argument and `18` as the number of months that you require for the export, the exported data would span the period between the first of January 2013 and the 31st of June 2014.

** Please note that the parameters should be passed in either enclosed within apostrophes '', or they should not be enclosed within any other characters such as backticks or quotes in order for the script to work correctly. **

# Notes and Assumptions

The script is really simple. It could be improved by adding a 4th parameter to indicate whether the generated file should be read on Unix or Windows (the assumed is Windows), and also the parameters should be checked for being provided in the proper format. I am aware that putting the execution logic into a separate class could have been better, but I think for such a small project, it is acceptable to leave everything within a single PHP file to avoid excessive cluttering.