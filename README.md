Customer Profile Page for Magento
==================

Customer Profile for Magento
This module add the option to show a Public Profile Page of one client, currently with these options:

- Show Avatar
- Show Nickname
- Show Customer Loyalty Level
- Show Birthday
- Show Recently Bought Products
- Show Wishlist
- Friendly URL

Use at your own risk

## Screenshot
Profile Page
![ScreenShot](https://raw.githubusercontent.com/blopa/Magento-Customer-Profile/master/screenshot/screenshot1.png)

My Account Page
![ScreenShot](https://raw.githubusercontent.com/blopa/Magento-Customer-Profile/master/screenshot/screenshot2.png)

## Layout
All style is done directly on the HTML, you can easly customize the layout with your own CSS file or editing these two files:
Profile Page: app/design/frontend/base/default/template/werules_customerprofile_view.phtml

Profile Settings: app/design/frontend/base/default/template/werules_customerprofile_config.phtml

## Instalation

1. Add the "app" folder to your ROOT magento install folder.

2. Clear Cache

3. Enjoy


ps. For some reason the database is not being created in some magento installations. I'm looking into it. For now you can check the file ```app/code/community/Werules/Customerprofile/sql/customerprofile_setup/mysql4-install-0.0.1.php``` and create a table on your database just like it shows on this file
