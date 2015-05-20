<?php
/**
 * helpers.php
 *
 * Contains various helper functions for generating markup.
 *
 * @package rdmgumby
 */

function generate_phone_link($phone) {
	$stripped_phone = str_replace(["-", "(", ")", " "], "", $phone);
	return "<a href='tel:" . $stripped_phone . "'>" . $phone . "</a>";
}