<?php
include '../config.php';

if(isset($_POST['add'])){
  $name = $_POST['name'];
  $icon = $_POST['icon'];
  $parent = $_POST['parent'] ?: NULL;

  mysqli_query($conn,"INSERT INTO categories (name,icon,parent_id) VALUES ('$name','$icon','$parent')");
}
?>

<form method="post">
  <input type="text" name="name" placeholder="Category Name" required>
  <input type="text" name="icon" placeholder="Emoji or Icon">

  <select name="parent">
    <option value="">Main Category</option>
    <?php
    $cats = mysqli_query($conn,"SELECT * FROM categories WHERE parent_id IS NULL");
    while($c = mysqli_fetch_assoc($cats)){
      echo "<option value='{$c['id']}'>{$c['name']}</option>";
    }
    ?>
  </select>

  <button name="add">Add Category</button>
</form>
