<?php
	/*
	Plugin Name: JM Government Widgets
	Plugin URI: http://gplit.com
	Description: JM Government Widgets is a plugin that allows you to display any of the listed government widgets in a WordPress widget.
	Version: 1.0.1
	Author: Josten Moore
	Author URI: http://gplit.com
	License: GPL2
	*/
	
	/*  Copyright 2011  JM Government Widgets  (http://gplit.com/about/contact/)

		This program is free software; you can redistribute it and/or modify
		it under the terms of the GNU General Public License, version 2, as 
		published by the Free Software Foundation.

		This program is distributed in the hope that it will be useful,
		but WITHOUT ANY WARRANTY; without even the implied warranty of
		MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
		GNU General Public License for more details.

		You should have received a copy of the GNU General Public License
		along with this program; if not, write to the Free Software
		Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
	*/
	
	$jm_gov_widgets_title = "JM Government Widgets";
	$jm_gov_widgets_title_stripped = str_replace(" ", "-", $jm_gov_widgets_title);
	
	//The widget display name
	$jm_gov_widgets_name = "Government Widgets";
	
	//jm_gov_table_name is a global variable the for the name of the table that the quotes are stored in
	define("jm_gov_table_name", "$wpdb->prefix" . "jm_gov_widgets");
	
	class Widget_JM_Gov_Widgets extends WP_Widget {
		function Widget_JM_Gov_Widgets() {
			global $jm_gov_widgets_title;
			global $jm_gov_widgets_title_stripped;
		
			$widget_options = array(
				'classname' => $jm_gov_widgets_title_stripped,
				'description' => __("$jm_gov_widgets_title")
			);
			
			$control_options = array(
				'height' => 600,
				'width' => 300
			);
			
			$this->WP_Widget("JM-Gov-Widgets", __("$jm_gov_widgets_title"), $widget_options, $control_options);
		}
		
		function widget($args, $instance) {
			global $wpdb;
			
			$sql = "SELECT * FROM " . jm_gov_table_name;
			
			$row = $wpdb->get_var($sql, 0, get_option('jm_gov_widgets_row'));
			_e($row);
		}
		
		function form($instance) {
			_e(" All configuration is done in Settings > JM Government Widgets.");
		}
		
		function update($new_instance, $old_instance) {}
	}
	
	//Embedded selection
	if(isset($_POST["jm_gov_embeds"]) && isset($_POST["radio_use"]))
	{
		global $wpdb;
		
		//When form is submitted; the specified row will be stored in this variable
		$selected_row = $_POST["radio_use"];
		update_option('jm_gov_widgets_row', $selected_row);
	}

	function jm_gov_widgets_init_config()
	{
		add_options_page("JM Government Widgets", "JM Government Widgets", "manage_options", "government_widgets", "jm_gov_widgets_create_admin_form");
		jm_gov_widgets_create_table();
	}
	
	function jm_gov_widgets_create_admin_form()
	{
		echo "<h3>Choose an embedded government widget to use</h3>";
		
		$jm_gov_embeds = jm_gov_widgets_get_embeds();
	
		if($jm_gov_embeds != NULL)
		{
			echo "<form action=\"\" method=\"post\">";
			echo "<table class=\"jm_gov_embeds\" style=\"width: auto;\">";
			echo "<tr>";
				echo "<td style=\"padding: 15px\"><h4>Embeds</h4></td>";
				echo "<td><h4>Choose a widget</h4></td>";
			echo "</tr>";
			
			for($i = 0; $i < sizeof($jm_gov_embeds); $i++)
			{
				$color = "white";
				if(($i % 2) == 0) { $color = "LightGrey"; }
				
				$embeds = $jm_gov_embeds[$i][0];
				
					echo "<tr style=\"background-color: $color\">";
						echo "<td style=\"padding: 15px;\">$embeds</td>";
						echo "<td style=\"padding: 15px;\"><input type=\"radio\" name=\"radio_use\" value=\"$i\"/></td>";
					echo "</tr>";
			}
			echo "</table>";
			echo "<input type=\"submit\" name=\"jm_gov_embeds\" value=\"Submit\"/>";
			echo "</form>";
		}
	}
	
	//Retrieves the widgets from the database (if any)
	function jm_gov_widgets_get_embeds()
	{
		global $wpdb;
		$sql = "SELECT * FROM " . jm_gov_table_name;
		
		//Stores the total amount of widgets in the database
		$rowCount = $wpdb->query($sql);
		
		$jm_gov_embeds = array(array());
		
		if($rowCount > 0)
		{
			for($i = 0; $i < $rowCount; $i++)
			{
				$embed = $wpdb->get_var($sql, 0, $i);
				
				$jm_gov_embeds[$i][] = $embed;
			}
			
			return $jm_gov_embeds;
		} else if($rowCount <= 0)
		{
			return NULL;
		}
	}
	
	//Create the table if it doesn't exist already
	function jm_gov_widgets_create_table()
	{
		global $wpdb;
		
		$sql = 	"CREATE TABLE IF NOT EXISTS " . jm_gov_table_name . "(
				embedded varchar(5000) NOT NULL
				);";
		
		$wpdb->query($sql);
		
		jm_gov_widgets_insert_into_table();
	}
	
	function jm_gov_widgets_insert_into_table()
	{
		global $wpdb;
		
		$jm_gov_embeds = array(
			0 => "<iframe width=\"220\" height=\"390\" scrolling=\"no\" marginheight=\"0\" marginwidth=\"0\" frameborder=\"0\" src=\"http://www.fema.gov/help/widgets/eastest.html\" title=\"Nationwide Test of the Emergency Alert System\"></iframe>",
			1 => "<iframe width=\"240\" height=\"400\" scrolling=\"no\" marginheight=\"0\" marginwidth=\"0\" frameborder=\"0\" src=\"http://www.fema.gov/help/widgets/npm.html\" title=\"Are You Prepared?\"></iframe>",
			2 => "<iframe width=\"240\" height=\"490\" scrolling=\"no\" marginheight=\"0\" marginwidth=\"0\" frameborder=\"0\" src=\"http://www.fema.gov/help/widgets/prepared_hurricane.html\" title=\"Are you prepared for hurricanes?\"></iframe>",
			3 => "<iframe width=\"240\" height=\"400\" scrolling=\"no\" marginheight=\"0\" marginwidth=\"0\" frameborder=\"0\" src=\"http://www.fema.gov/help/widgets/prepared_flooding.html\" title=\"Are you Prepared for flooding?\"></iframe>",
			4 => "<iframe width=\"250\" height=\"420\" scrolling=\"no\" marginheight=\"0\" marginwidth=\"0\" frameborder=\"0\" src=\"http://www.fema.gov/help/widgets/da_main.html\" title=\"Are you a disaster survivor?\"></iframe>",
			5 => "<iframe width=\"300\" height=\"250\" scrolling=\"no\" frameborder=\"0\" src=\"http://www.fema.gov/help/widgets/sfi/seasonal_flood_insurance.html\" title=\"Seasonal Flood Insurance\"></iframe>",
			6 => "<iframe width=\"240\" height=\"490\" scrolling=\"no\" marginheight=\"0\" marginwidth=\"0\" frameborder=\"0\" src=\"http://www.fema.gov/help/widgets/kids_fire.html\" title=\"Kids Fire Safety\"></iframe>",
			7 => "<iframe width=\"260\" height=\"540\" scrolling=\"no\" marginheight=\"0\" marginwidth=\"0\" frameborder=\"0\" src=\"http://www.fema.gov/help/widgets/main.html\" title=\"Are You Prepared / Are You A Disaster Survivor?\"></iframe>",
			8 => "<p><a href=\"http://www.fema.gov/rebuildinglives\"><img alt=\"Rebuilding Lives, Revitalizing Communities - 5 Years after Katrina and Rita graphic\" src=\"http://www.fema.gov/graphics/media/2010/rebuilding-lives/rlrc_272_a.gif\" border=\"0\" /></a></p>",
			9 => "<iframe width=\"300\" height=\"250\" scrolling=\"no\" frameborder=\"0\" src=\"http://www.fema.gov/help/widgets/floodsmart/hurr_11.html\" title=\"Are You Ready for Hurricane Season?\"></iframe>",
			10 => "<iframe width=\"250\" height=\"250\" scrolling=\"no\" marginheight=\"0\" marginwidth=\"0\" frameborder=\"0\" src=\"http://www.fema.gov/help/widgets/privatesector.html\" title=\"Private Sector\"></iframe>",
			11 => "<iframe width=\"195\" height=\"145\" scrolling=\"no\" marginheight=\"0\" marginwidth=\"0\" frameborder=\"0\" src=\"http://www.citizencorps.gov/widgets/cclogo.shtm\" title=\"Citizen Corps\"></iframe>",
			12 => "<script src=\"http://www.usfa.dhs.gov/_scripts/smokealarm.js\"></script>"		
		);
		
		$sql = "SELECT * FROM " . jm_gov_table_name;
		$result = $wpdb->query($sql);
		
		if($result == 0) {
			foreach ($jm_gov_embeds as &$embedded) {
				$wpdb->insert(jm_gov_table_name, array("embedded" => $embedded));
			}
		}
	}
	
	function jm_gov_widgets_init_widget() {
		register_widget("Widget_JM_Gov_Widgets");
	}
	
	//Register config page
	add_action("admin_menu", "jm_gov_widgets_init_config");
	
	//Register widget
	add_action("widgets_init", "jm_gov_widgets_init_widget");
?>