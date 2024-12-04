# Databaze

# How to Connect XAMPP with PHP

To connect XAMPP with PHP, you can watch the following video:

[Watch the tutorial](https://youtu.be/XLTkcB_T8Mo?si=YTrYrGJsCF3HtDP-)

## File Configuration

In the `login.php` file, make sure to adjust the following configurations:

- Change the **server name** and **database** name to match your settings.
- Note that the database in the import file is named `shoppee` (incorrect spelling), while in `login.php`, it is correctly spelled as `shopee`.

## Temporary Test Instructions:

1. Open your web browser after starting Apache in XAMPP.
2. Navigate to:  
   `http://localhost:8080/Databaze/`  
   *(The port number may vary depending on your machine configuration)*

3. You will be redirected to the `index.html` page.
4. Enter user information into the login form.
5. Depending on the user information provided, you will be automatically redirected to the corresponding page (buyer or seller).
