<?php

class m160329_132730_view_with_region_and_country extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
	    CREATE VIEW
		view_regions_ro
	    AS SELECT
		id,
		name,
		short_name,
		code,
		timezone_name,
		country_id as country_code
	    FROM regions;
        ");


        $this->execute("
	    CREATE VIEW
		view_country_ro
	    AS SELECT
		code,
		alpha_3,
		name,
		in_use,
		lang,
		currency_id as currency_code
	    FROM country;
        ");
    }

    public function down()
    {
        $this->execute("DROP VIEW view_country_ro");
        $this->execute("DROP VIEW view_regions_ro");
    }
}