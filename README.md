# Convert Tags to Lowercase for WooCommerce

![WordPress Plugin Version](https://img.shields.io/badge/Version-1.1.0-blue)
![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue)
![WooCommerce](https://img.shields.io/badge/WooCommerce-5.0%2B-orange)
![PHP](https://img.shields.io/badge/PHP-7.2%2B-purple)
![License](https://img.shields.io/badge/License-GPL%20v2%2B-green)

A lightweight WordPress plugin that automatically converts all WooCommerce product tags to lowercase for consistent taxonomy management and duplicate prevention.

## 📋 Table of Contents

- [Features](#-features)
- [Installation](#-installation)
- [Usage](#-usage)
- [Screenshots](#-screenshots)
- [Configuration](#-configuration)
- [Translation](#-translation)
- [FAQ](#-frequently-asked-questions)
- [Troubleshooting](#-troubleshooting)
- [Development](#-development)
- [Changelog](#-changelog)
- [License](#-license)
- [Support](#-support)

## ✨ Features

### 🔧 Core Functionality
- **Automatic Conversion**: Tags convert to lowercase when creating/editing products
- **Real-time Processing**: Converts tags as you type in all interfaces
- **Bulk Operations**: Convert all existing tags with one click
- **Individual Control**: Convert specific tags individually with buttons
- **Bulk Actions**: WordPress bulk actions support for mass conversion

### 🌐 Compatibility
- **UTF-8 Support**: Handles special characters, accents, and international text
- **Translation Ready**: Includes English and Spanish translations
- **WooCommerce Compatible**: Works with WooCommerce 5.0+
- **No Database Changes**: Safe installation and removal

### 🛡️ Reliability
- **Error Handling**: Graceful error recovery and user feedback
- **Permission Checks**: Proper WordPress capability verification
- **Nonce Security**: All actions protected with WordPress nonces
- **Performance Optimized**: Minimal impact on site performance

## 📥 Installation

### Prerequisites
- WordPress 5.0 or higher
- WooCommerce 5.0 or higher
- PHP 7.2 or higher

### Method 1: WordPress Admin (Recommended)
1. Navigate to **Plugins → Add New** in your WordPress dashboard
2. Search for "Convert Tags to Lowercase for WooCommerce"
3. Click **Install Now**
4. Click **Activate**

### Method 2: Manual Upload
1. Download the latest release ZIP file
2. Go to **Plugins → Add New → Upload Plugin**
3. Select the downloaded ZIP file
4. Click **Install Now** then **Activate**

### Method 3: FTP/SFTP
1. Extract the plugin ZIP file to your computer
2. Upload the `wc-tags-lowercase` folder to `/wp-content/plugins/`
3. Go to **Plugins** in WordPress admin
4. Find "Convert Tags to Lowercase for WooCommerce" and click **Activate**

### Post-Installation
1. Verify WooCommerce is active
2. Visit **WooCommerce → Tags Lowercase** to access plugin tools
3. Test by adding a new product tag with uppercase letters

## 🚀 Usage

### Automatic Operation
The plugin works automatically in the background:
- **New Tags**: Automatically converted when added via any interface
- **Existing Tags**: Converted when edited or updated
- **Product Edits**: Tags attached to products are normalized on save
- **Quick Edit**: Inline editing respects lowercase conversion

### Manual Tools

#### 1. Convert All Existing Tags
1. Go to **WooCommerce → Tags Lowercase**
2. Review the statistics panel
3. Click **Convert All Tags** button
4. Confirm the action in the popup
5. View success message with conversion count

#### 2. Individual Tag Conversion
1. Navigate to **Products → Tags**
2. Tags with uppercase letters show a **Convert** button
3. Click to convert individual tags
4. See immediate visual feedback

#### 3. Bulk Actions
1. Go to **Products → Tags**
2. Select multiple tags using checkboxes
3. Choose **Convert to lowercase** from bulk actions dropdown
4. Click **Apply**
5. View notification with conversion count

#### 4. Real-time Interface
- **Add New Tag**: Type uppercase → see it convert as you type
- **Edit Tag**: Existing uppercase tags convert on save
- **Product Edit Screen**: Tags normalize when product is saved

## 🖼️ Screenshots

### 1. Admin Dashboard
![Admin Interface](https://via.placeholder.com/800x400/4a90e2/ffffff?text=Tags+Lowercase+Admin+Interface)
*Main plugin page with conversion tools and statistics*

### 2. Tags Management
![Tags List](https://via.placeholder.com/800x400/50b848/ffffff?text=Product+Tags+with+Conversion+Buttons)
*Product tags list showing uppercase tags with convert buttons*

### 3. Bulk Actions
![Bulk Actions](https://via.placeholder.com/800x400/f5a623/ffffff?text=Bulk+Actions+-+Convert+to+lowercase)
*Bulk actions dropdown with conversion option*

### 4. Real-time Conversion
![Real-time](https://via.placeholder.com/800x400/9013fe/ffffff?text=Real-time+Tag+Conversion)
*Tag input field showing real-time lowercase conversion*

## ⚙️ Configuration

### Filter Hooks

#### Skip Conversion for Specific Tags
```php
/**
 * Skip conversion for specific tag names
 * @param bool $skip Whether to skip conversion
 * @param string $tag_name The tag name being processed
 * @return bool Modified skip value
 */
add_filter('wc_tags_lowercase_skip_conversion', function($skip, $tag_name) {
    // Skip conversion for VIP and NEW tags
    if (in_array(strtoupper($tag_name), ['VIP', 'NEW', 'FEATURED'])) {
        return true;
    }
    return $skip;
}, 10, 2);
```

## 🌐 Translation
### Available Translations
English (US) - Default, included

Spanish (Mexico) - Included (es_MX)

Translation Template - .pot file for creating new translations

Adding New Translations
Using Poedit (Recommended)
