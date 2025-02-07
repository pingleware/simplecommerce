# Newsletter 

## Use Cases
An **eCommerce newsletter** is a powerful tool for engaging customers, increasing sales, and building brand loyalty. Here are some key **use cases** for an eCommerce newsletter:  

---

### **1. Promotions & Discounts**  
**Use Case:** Send exclusive offers, flash sales, and discount codes to subscribers.  
**Example:** "Get 20% off your next purchase! Limited-time offer inside."  

---

### **2. New Product Announcements**  
**Use Case:** Notify customers about new product launches or restocked items.  
**Example:** "Introducing our latest collection – shop now before it's gone!"  

---

### **3. Abandoned Cart Recovery**  
**Use Case:** Remind customers about items left in their shopping carts.  
**Example:** "Forgot something? Your cart is waiting for you – complete your purchase now!"  

---

### **4. Seasonal & Holiday Campaigns**  
**Use Case:** Send holiday-themed promotions, gift guides, and special deals.  
**Example:** "Holiday Gift Guide: Perfect gifts for everyone on your list!"  

---

### **5. Customer Loyalty & Rewards**  
**Use Case:** Reward repeat customers with loyalty points or exclusive perks.  
**Example:** "You're a VIP! Enjoy early access to our newest collection."  

---

### **6. Personalized Recommendations**  
**Use Case:** Use past purchase behavior to suggest products.  
**Example:** "Based on your recent order, you might love these!"  

---

### **7. Company Updates & Announcements**  
**Use Case:** Share business milestones, store openings, or policy changes.  
**Example:** "We’re growing! Check out our new store location."  

---

### **8. Educational Content & How-To Guides**  
**Use Case:** Provide value through product tutorials, styling tips, or industry trends.  
**Example:** "5 ways to style your new dress – fashion tips inside!"  

---

### **9. Customer Testimonials & Social Proof**  
**Use Case:** Showcase reviews and testimonials to build trust.  
**Example:** "See what our customers are saying about our best-selling shoes!"  

---

### **10. Subscription & Membership Reminders**  
**Use Case:** Notify customers about upcoming renewals or membership perks.  
**Example:** "Your subscription is about to renew – enjoy exclusive benefits!"  

---
## Template Design
Here's a **responsive HTML newsletter template** for your **SimpleCommerce eCommerce platform**. It follows best practices for email compatibility and includes sections for promotions, new arrivals, and CTA buttons.

---

### **Features:**
✅ Mobile-friendly (responsive design)  
✅ Uses inline CSS for email client compatibility  
✅ Includes a call-to-action (CTA) for better conversions  
✅ Clean, professional layout  

---

### **HTML Newsletter Template**
```html
<!DOCTYPE html>
<html>
<head>
    <title>SimpleCommerce Newsletter</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            background: #333;
            color: #ffffff;
            padding: 20px;
            font-size: 24px;
        }
        .content {
            padding: 20px;
            text-align: center;
        }
        .button {
            display: inline-block;
            background: #ff6600;
            color: #ffffff;
            padding: 12px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .footer {
            background: #333;
            color: #ffffff;
            text-align: center;
            padding: 10px;
            font-size: 14px;
            margin-top: 20px;
        }
        .product {
            display: inline-block;
            width: 48%;
            margin: 1%;
            text-align: center;
        }
        .product img {
            max-width: 100%;
            border-radius: 8px;
        }
        @media (max-width: 600px) {
            .product {
                width: 100%;
                margin: 0 0 10px 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">🚀 Welcome to SimpleCommerce!</div>

        <div class="content">
            <h2>Exclusive Offers Just for You</h2>
            <p>Enjoy special discounts on our latest products.</p>
            <a href="https://my-buy-it-now.com" class="button">Shop Now</a>
        </div>

        <div class="content">
            <h3>New Arrivals</h3>
            <div class="product">
                <img src="https://via.placeholder.com/200" alt="Product 1">
                <p><strong>Product Name 1</strong></p>
                <p>$29.99</p>
            </div>
            <div class="product">
                <img src="https://via.placeholder.com/200" alt="Product 2">
                <p><strong>Product Name 2</strong></p>
                <p>$39.99</p>
            </div>
        </div>

        <div class="content">
            <p>Follow us for more updates:</p>
            <a href="#">Facebook</a> | <a href="#">Instagram</a> | <a href="#">Twitter</a>
        </div>

        <div class="footer">
            &copy; 2024 SimpleCommerce. All rights reserved.<br>
            <a href="#" style="color: #ff6600;">Unsubscribe</a>
        </div>
    </div>
</body>
</html>
```

---

### **How to Use This Template**
1. Replace the **placeholder product images** (`https://via.placeholder.com/200`) with actual product images.
2. Update **product names** and **prices**.
3. Replace `https://my-buy-it-now.com` with your store’s URL.
4. Customize colors to match your brand.

---

## Database Table
Here's how you can create an **SQLite** table for storing newsletter subscriptions by **Stripe customer ID**, and a **PHP script** to check if an email is already a Stripe customer. If not, it will create a new Stripe customer and store the information.  

---

### **Step 1: Create the SQLite Table**
```sql
CREATE TABLE IF NOT EXISTS newsletter_subscriptions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    stripe_customer_id TEXT UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```
This table stores:
- `stripe_customer_id`: The unique identifier from Stripe  
- `created_at`: Timestamp of the subscription  

---

### **Step 2: PHP Script to Check or Create a Stripe Customer**
```php
<?php
require 'vendor/autoload.php'; // Load Composer dependencies
\Stripe\Stripe::setApiKey('your-stripe-secret-key'); // Set your Stripe secret key

// Connect to SQLite database
$db = new SQLite3('simplecommerce.db');

// Function to check if an email exists in the database
function getStripeCustomerId($email, $db) {
    $stmt = $db->prepare("SELECT stripe_customer_id FROM newsletter_subscriptions WHERE email = :email");
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);
    return $row ? $row['stripe_customer_id'] : null;
}

// Function to create a new Stripe customer
function createStripeCustomer($email) {
    try {
        $customer = \Stripe\Customer::create([
            'email' => $email,
            'description' => 'Newsletter Subscription'
        ]);
        return $customer->id;
    } catch (\Stripe\Exception\ApiErrorException $e) {
        die("Stripe Error: " . $e->getMessage());
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format");
    }

    // Check if email already exists in the database
    $stripeCustomerId = getStripeCustomerId($email, $db);

    if (!$stripeCustomerId) {
        // Create a new Stripe customer
        $stripeCustomerId = createStripeCustomer($email);

        // Insert into the database
        $stmt = $db->prepare("INSERT INTO newsletter_subscriptions (stripe_customer_id, email) VALUES (:stripe_customer_id, :email)");
        $stmt->bindValue(':stripe_customer_id', $stripeCustomerId, SQLITE3_TEXT);
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $stmt->execute();

        echo "Subscription successful!";
    } else {
        echo "You're already subscribed!";
    }
}
?>

<form method="post">
    <input type="email" name="email" placeholder="Enter your email" required>
    <button type="submit">Subscribe</button>
</form>
```

---

### **How It Works:**
1. **Checks if the email exists** in the `newsletter_subscriptions` table.  
2. If it **doesn’t exist**, it **creates a new Stripe customer** using Stripe’s API.  
3. Stores the **Stripe Customer ID** and email in the SQLite database.  
4. If the email is **already subscribed**, it **prevents duplicate entries**.  

---

### **Next Steps**
Would you like me to add:
✅ **AJAX submission** for a smoother UX?  
✅ **Email confirmation** after subscribing?  

