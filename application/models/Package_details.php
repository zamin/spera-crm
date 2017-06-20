<?php

class Package_details extends ActiveRecord\Model {

	static $has_many = array(
    array("tickets"),
    );
}
