<?php
/*
Plugin Name: Rate Anything!
Plugin URI: URI_Of_Page_Describing_Plugin_and_Updates
Description: Rate Anything adds a rating list to your blog, allowing you to set up your own categoried and give items in it a rating.
Version: 0.1
Author: Curt Hasselschwert
Author URI: http://www.brilliantpixels.com
*/

if (isset($_GET['activate']) && $_GET['activate'] == 'true') {
   add_action('init', 'rate_anything_install');
}

function rate_anything_panel_init() {
    if (function_exists('add_options_page')) {
			add_management_page('Rate Anything', 'Rate Anything', 8, basename(__FILE__), 'rate_anything_subpanel');
    }
}

function rate_anything_install() {
   global $table_prefix, $wpdb, $user_level;

   $rating_table_name = $table_prefix . "itemrating";
   $category_table_name = $table_prefix . "ratingcat";

   get_currentuserinfo();
   if ($user_level < 8) { return; }

   if($wpdb->get_var("show tables like '$table_name'") != $rating_table_name) {
      
      $sql = "CREATE TABLE ".$rating_table_name." (
	      id mediumint(9) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
	      catid mediumint(9) UNSIGNED ZEROFILL NOT NULL,
        name tinytext NOT NULL,
        rating smallint(1) UNSIGNED NOT NULL,
	      UNIQUE KEY id (id)
	     );";

      require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
      dbDelta($sql);

   }
   
   if($wpdb->get_var("show tables like '$table_name'") != $category_table_name) {
      
      $sql = "CREATE TABLE ".$category_table_name." (
	      id mediumint(9) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
        name tinytext NOT NULL,
        description text,
	      UNIQUE KEY id (id)
	     );";

      require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
      dbDelta($sql);

   }
   
}
 
function rate_anything_subpanel() {
  if (isset($_POST['submit'])) {
  	
  	switch($_POST['submit']) {
  		case 'Add Item':
  			$result = rate_anything_add_item();
  			break;
  		case 'Update Ratings':
  			$result = rate_anything_item_update();
  			break;
  		case 'Add Category':
  			$result = rate_anything_add_cat();
  			break;
  		case 'Delete Category':
  			$result = rate_anything_delete_cat();
  			break;
  		case 'Update Category':
  			$result = rate_anything_update_cat();
  			break;
  		}
    ?>
    <div class="updated"><p><strong>
    <pre>
    <?php 
			echo $result;
    ?>
    </pre>
    </strong></p></div><?php
	} ?>
<div class=wrap>
	<form method="post">
    <h2>Rating Manager</h2>
    <fieldset name="set1">
			<legend>Categories</legend>
				<? rate_anything_cat_display() ?>
    </fieldset>
    <fieldset name="set1">
			<legend>Add Rating Category</legend>
				Category Name:<br />
				<input type="text" size="20" name="catname" /><br />
				Category Description:<br />
				<textarea rows="4" cols="30" name="catdescription">
				</textarea>
			<div class="submit">
		  	<input type="submit" name="submit" value="Add Category" />
			</div>
    </fieldset>
	</form>
  <form method="post">
    <fieldset name="set1">
			<legend>Modify Ratings</legend>
				<? rate_anything_item_display_modify() ?>
			<div class="submit">
		  	<input type="submit" name="submit" value="Update Ratings" />
			</div>
    </fieldset>
	</form>
	<form method="post">
    <fieldset name="set2">
			<legend>Add New Item</legend>
			<div style="padding: 15px;">
				<div class="assoc_name_box">
					Name: <input type="text" name="name" size="10" /><br />
					Category: <? rate_anything_cat_dropdown() ?>
				</div>
					<table id="rating_table">
						<tr>
							<td>Rating:</td>
							<td><input type="radio" value="0" name="rating" /></td>
							<td><input type="radio" value="1" name="rating" /></td>
							<td><input type="radio" value="2" name="rating" /></td>
							<td><input type="radio" value="3" name="rating" /></td>
							<td><input type="radio" value="4" name="rating" /></td>
							<td><input type="radio" value="5" name="rating" /></td>
						</tr>
						<tr>
							<td></td>
							<td>0</td>
							<td>1</td>
							<td>2</td>
							<td>3</td>
							<td>4</td>
							<td>5</td>
						</tr>
					</table>	
			</div>
			<div class="submit">
		  	<input type="submit" name="submit" value="Add Item" />
			</div>
    </fieldset>
  </form>
 </div><?php
}

function rate_anything_add_cat() {
	global $table_prefix, $wpdb;
	$table_name = $table_prefix . "ratingcat";
	
	$sql = "INSERT INTO ".$table_name.
         " (name,description) ".
         "VALUES ('".$_POST['catname']."','".$_POST['catdescription']."')";
	
	$results = $wpdb->query( $sql );
	
	if($results)
		return 'Category successfully added!';
	else
		return 'Category could not be added.';
}

function rate_anything_cat_display() {
	global $table_prefix, $wpdb;
	$table_name = $table_prefix . "ratingcat";
	
	$sql = "SELECT * FROM $table_name";
	$results = $wpdb->get_results($sql,ARRAY_A);
	
	if(count($results) > 0) {
		
		foreach($results as $row) {
			$output = "<form method=\"post\"><input type=\"hidden\" name=\"catid\" value=\"" . $row['id'] . "\" /><table>";
			$output .= "\n\t<tr>";
			$output .= "\n\t\t<td valign=\"top\"><input type=\"text\" name=\"name\" size=\"20\" value=\"" . $row['name'] . "\" /></td>";
			$output .= "\n\t\t<td valign=\"top\"><textarea name=\"description\" rows=\"2\" cols=\"20\">" . $row['description'] . "</textarea></td>";
			$output .= "\n\t\t<td valign=\"top\"><input type=\"submit\" name=\"submit\" value=\"Delete Category\" /> <input type=\"submit\" name=\"submit\" value=\"Update Category\" /></td>";
			$output .= "\n\t</tr>";
			$output .= "</table></form>";
			echo $output;
		}
	
	}
	
}

function rate_anything_cat_dropdown() {
	global $table_prefix, $wpdb;
	$table_name = $table_prefix . "ratingcat";
	
	$sql = "SELECT * FROM $table_name";
	$results = $wpdb->get_results($sql,ARRAY_A);
	
	if(count($results) > 0) {
		
		$output = "<select name=\"category\">";
		
		foreach($results as $row) {
			
			$output .= "\n\t<option value=\"" . $row['id'] . "\">";
			$output .= $row['name'];
			$output .= "</option>";
			
		}
		
		$output .= "</select>";
		echo $output;
	
	}
	
}

function rate_anything_delete_cat() {
	global $table_prefix, $wpdb;
	$table_name = $table_prefix . "ratingcat";
	$rating_table = $table_prefix . "itemrating";
	
	$sql = "DELETE FROM $table_name WHERE id = " . $_POST['catid'];
	$result = $wpdb->query( $sql );
	
	$sql = "DELETE FROM $rating_table WHERE catid = " . $_POST['catid'];
	$wpdb->query( $sql );
	
	if($result)
		return "Category Deleted!";
	else
		return "Category could not be deleted!";
}

function rate_anything_update_cat() {
	global $table_prefix, $wpdb;
	$table_name = $table_prefix . "ratingcat";
	
	$sql = "UPDATE $table_name SET name = '" . $_POST['name'] . "',description = '" . $_POST['description'] . "' WHERE id = " . $_POST['catid'];
	$result = $wpdb->query( $sql );
	
	if($result)
		return "Category Updated!";
	else
		return "Category could not be updated!";
}

function rate_anything_add_item() {
	global $table_prefix, $wpdb;
	$table_name = $table_prefix . "itemrating";
	
	$sql = "INSERT INTO ".$table_name.
         " (catid,name,rating) ".
         "VALUES ('".$_POST['category']."','".$_POST['name']."','".$_POST['rating']."')";
	
	$results = $wpdb->query( $sql );
	
	if($results)
		return 'Associate successfully added!';
	else
		return 'Associate could not be added.';
}

function rate_anything_item_display_modify() {
	global $table_prefix, $wpdb;
	$table_name = $table_prefix . "itemrating";
	$cat_table_name = $table_prefix . "ratingcat";
	
	$sql = "SELECT id,name FROM $cat_table_name";
	$categories = $wpdb->get_results($sql,ARRAY_A);
	
	if(count($categories) > 0) {
	
		foreach($categories as $category) {
			echo "<h3>" . $category['name'] . "</h3>";
			
			$sql = "SELECT * FROM $table_name WHERE catid = " . $category['id'];
			$results = $wpdb->get_results($sql,ARRAY_A);
			
			if (count($results) > 0) {
			foreach ($results as $row) {?>
				<div style="padding: 5px 10px;">
						<div class="assoc_name_box"><? echo $row['name'] ?></div>
							<table id="rating_table">
								<tr>
									<td>Rating:</td><?
									for ($i = 0; $i <= 5; $i++) {
										$celldata = "\n<td><input type=\"radio\" value=\"$i\" name=\"" . $row['id'] ."\" ";
										if ($row['rating'] == $i)
											$celldata .= "checked=\"checked\"";
										$celldata .= "/></td>";
										echo $celldata;
									}
									echo "\n<td class=\"delete_cell\"><input type=\"radio\" value=\"delete\" name=\"".$row['id']."\" /></td>";
									?>
								</tr>
								<tr>
									<td></td>
									<td>0</td>
									<td>1</td>
									<td>2</td>
									<td>3</td>
									<td>4</td>
									<td>5</td>
									<td class="delete_cell">delete</td>
								</tr>
							</table>			
					</div><?
				}
			}
				
		}	
	
	}
	
}

function rate_anything_item_update() {
	global $table_prefix, $wpdb;
	$table_name = $table_prefix . "itemrating";
	
	while (list($key, $value) = each($_POST)) {
		if ($key != submit) {
			if ($value == 'delete') {
				$sql = "DELETE FROM $table_name WHERE id = $key";
			} else {
				$sql = "UPDATE $table_name SET rating = $value WHERE id = $key";
			}
			$wpdb->query( $sql );
		}
	}
	
	return "Update successful!";
}

function rate_anything_display() {
	global $table_prefix, $wpdb;
	$table_name = $table_prefix . "itemrating";
	$cat_table_name = $table_prefix . "ratingcat";
	$images_dir = '/wp-content/plugins/rate-anything/';
	
	$sql = "SELECT * FROM $cat_table_name";
	$categories = $wpdb->get_results($sql,ARRAY_A);
	
	if (count($categories) > 0) {
		
		foreach ($categories as $category) {
			$sql = "SELECT name,rating FROM $table_name WHERE catid = " . $category['id'] . " ORDER BY rating DESC";
			$results = $wpdb->get_results($sql,ARRAY_A);
			
			if (count($results) > 0) {
				$output  = "\n<h3 class=\"rate-anything-header\">" . $category['name'] . "</h3>";
				$output .= "\n<p class=\"rate-anything-desc\">" . $category['description'] . "</p>";
				$output .= "\n<table class=\"rate-anything-table\">";
				foreach ($results as $row) {
					$image_path = $images_dir . $row['rating'] . "_stars.gif";
					$output .= "\n\t<tr>\n\t\t<td width=\"50%\" class=\"rate-anything-item\">" . $row['name'] . "</td><td class=\"rate-anything-rating\"><img src=\"$image_path\" width=\"86\" height=\"16\" /></td>\n\t</tr>";	
				}
				$output .= "\n</table>";
			}
			echo $output;
		}
		
			
	}
	
}

function asrating_css() {
	echo "
	<style type='text/css'>
	.assoc_name_box {
		width: 200px;
		float: left;
	}
	#rating_table {
		margin: 0;
		padding: 0;
		border-collapse: collapse;
	}
	#rating_table td {
		text-align: center;
		padding: 0 3px;
	}
	#rating_table td.delete_cell {
		padding: 0 5px;
		background: #bbbbbb;
	}
	</style>
	";
}

add_action('admin_head', 'asrating_css');
add_action('admin_menu', 'rate_anything_panel_init');

?>