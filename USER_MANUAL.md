# Linkin Art Inventory System User Manual

## Table of Contents
1. [System Overview](#system-overview)
2. [Getting Started](#getting-started)
    - [Logging In](#logging-in)
    - [Logging Out](#logging-out)
3. [Navigation & Layout](#navigation--layout)
4. [Main Features](#main-features)
    - [Dashboard](#dashboard)
    - [Inventory Management](#inventory-management)
    - [Categories](#categories)
    - [Stock In/Out](#stock-inout)
    - [Search](#search)
    - [Variations & Date in Stock](#variations--date-in-stock)
5. [Managing Items](#managing-items)
    - [Add New Item](#add-new-item)
    - [Edit Item](#edit-item)
    - [Delete Item](#delete-item)
6. [Theme Switching](#theme-switching)
7. [Notifications](#notifications)
8. [Security & Logout](#security--logout)
9. [Troubleshooting & FAQ](#troubleshooting--faq)
10. [Support & Contact](#support--contact)

---

## System Overview
Linkin Art Inventory is a web-based inventory management system designed for local network use. It allows admins and staff to manage materials, supplies, categories, and stock levels efficiently, with secure login and user-friendly features.

---

## Getting Started

### Logging In
- **Admin:** Go to `/admin_login.php` and enter your admin credentials.
- **Staff:** Go to `/staff_login.php` and enter your staff credentials.
- If you do not have an account, contact your system administrator.

### Logging Out
- Click the **Logout** button in the top navigation bar at any time to securely log out.
- After logout, you will be redirected to the login page.

---

## Navigation & Layout
- The sidebar/menu provides access to all main sections: Dashboard, Inventory, Categories, Stock In, Stock Out, and Search.
- The top bar includes theme switching and logout.
- The main content area displays the selected section.

---

## Main Features

### Dashboard
- View a summary of categories, available materials/supplies, and inventory statistics.
- Quick access to key actions (e.g., manage inventory, view categories).

### Inventory Management
- View all products/materials and their details.
- Add, edit, or delete items (admin only).
- See stock levels and variations.

### Categories
- View, add, or delete categories (admin only).
- Filter inventory by category.

### Stock In/Out
- **Stock In:** Add new stock for existing items, including the date in stock.
- **Stock Out:** Record when items are used or removed from inventory.
- View stock in/out logs and details.

### Search
- Use the **Search** menu to find items by category or by material/supply name.
- Search results update dynamically (AJAX-powered).

### Variations & Date in Stock
- Manage item variations (e.g., size, color).
- Each variation tracks its own stock and "Date in Stock."

---

## Managing Items

### Add New Item
1. Go to the Inventory section.
2. Click the **Add** button.
3. Fill in the item details (name, category, etc.).
4. Click **Save** to add the item.

### Edit Item
1. In the Inventory list, find the item you want to edit.
2. Click the **Edit** button next to the item.
3. Update the details as needed.
4. Click **Save** to apply changes.

### Delete Item
1. In the Inventory list, find the item you want to delete.
2. Click the **Delete** button next to the item.
3. Confirm the deletion when prompted.

---

## Theme Switching
- Click the theme toggle button (sun/moon icon) in the top bar to switch between light and dark themes.
- Your preference is saved for future visits.

---

## Notifications
- System notifications (success, error, info) appear as animated pop-ups in the top-right corner.
- These notify you of actions like successful saves, errors, or important updates.

---

## Security & Logout
- Always log out when finished to protect your account.
- The system prevents unauthorized access to protected pages after logout.
- You cannot use the browser back button to return to login or protected pages after logging out.
- Sessions may expire after inactivity for security.

---

## Troubleshooting & FAQ

**Q: I can’t log in.**
- Check your username and password.
- Make sure Caps Lock is off.
- Contact your admin if you forgot your credentials.

**Q: The logout button is not visible.**
- Try switching themes or refreshing the page.
- Clear your browser cache.
- Contact your admin if the issue persists.

**Q: I can’t see new items or changes.**
- Refresh the page.
- Clear your browser cache.

**Q: I get a session timeout message.**
- Log in again. Sessions expire after inactivity for security.

**Q: How do I access the system from another device?**
- Make sure your device is connected to the same local network as the server.
- Use the server’s local IP address in your browser (e.g., `http://192.168.1.10/linkin_art_inventory/`).

---

## Support & Contact
- For help, contact your system administrator or the person who set up the inventory system.
- For technical issues, check the logs in `/logs/notification.log` or contact your IT support.

---

*End of Manual* 