# simplecommerce
A lightweight, open-source eCommerce framework that integrates Stripe for products, payments, and customer data, prioritizing security and simplicity. With no PII stored on public servers, role-based extensibility, and zero paywalls, SimpleCommerce is the hassle-free solution for modern online stores. 

## Prerequities

- PHP 7.x
- PHP Sqlite3 module enabled
- PHP curl module enabled
- composer stripe library (included)


## **Quickstart Guide for WSL**
This guide walks you through setting up **SimpleCommerce** on **Windows Subsystem for Linux (WSL)**.

---

### **1. Install WSL**  
Open **PowerShell as Administrator** and run:  
```powershell
wsl --install
```
If you already have WSL installed, make sure it's up to date:  
```powershell
wsl --update
```
Restart your PC if prompted.

---

### **2. Install PHP & Required Components**  
Launch **WSL** and install the necessary dependencies:  
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y php php-cli php-sqlite3 unzip curl git composer
```

---

### **3. Clone SimpleCommerce Repository**  
Navigate to your preferred directory and clone the repo:  
```bash
cd ~
git clone https://github.com/pingleware/simplecommerce.git
cd simplecommerce
```

---

### **4. Start WSL from the Project Directory**  
To quickly launch WSL in this directory from **Windows**, run:  
```powershell
wsl ~ -d Ubuntu -e bash -c "cd ~/simplecommerce && bash"
```

---

### **5. Install Dependencies**  
Inside WSL, run:  
```bash
composer update
```

---

### **6. Start the Development Server**  
Run the built-in PHP server:  
```bash
php -S localhost:8080 -t .
```
Your SimpleCommerce site is now running at:  
🔗 [http://localhost:8080](http://localhost:8080)

---


## Features

- No personal identifying information kept on public server
- Stripe payment and product inventory hosting
- Gift card purchase between giver and receiver
- Coupons and promotion codes for periodic discounts
- Newsletter creation and subscription containing selected products
- ShipStation integration
- USPS click-n-ship export
- Sitemap and robots.txt creation

## Official User Guide
**COMING SOON!**

## Live Preview

[my-buy-it-now.com](https://my-buy-it-now.com)

## In the News
While SimpleCommerce is **NOT** mention the following articles, they do mention eCommerce as a viable business.

- [The Best Side Hustles To Start In 2025 For Maximum Profitability](https://www.forbes.com/sites/melissahouston/2025/02/07/the-best-side-hustles-to-start-in-2025-for-maximum-profitability/)
