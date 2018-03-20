# mysqlconnector
The current version of the script takes care of the following conditions

1. Creates a table if its missing
2. Inserts data into the table from post json
3. Updates the data if the same row is send again
4. Alters the data table with newly added columns from the JSON POST


You need to following the instructions to make this script works
It current works for single level parent form only

1. setup a webserver and install this folder
2. setup the correct mysql configurations in the includes/config file
3. setup the endpoint in your iform http POST section
4. test all the conditions before deploying it on production forms.