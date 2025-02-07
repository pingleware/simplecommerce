<!-- Footer -->
<footer class="w3-padding-64 w3-light-grey w3-small w3-center" id="footer">
    <div class="w3-row-padding">
        <div class="w3-col s4">
            <h4>Contact</h4>
            <p>Questions? Go ahead.</p>
            <form id="emailForm" action="mailto:<?php echo $settings['contact-email']; ?>" method="post" enctype="text/plain" target="_blank">
                <p><input class="w3-input w3-border" type="text" placeholder="Name" name="Name" required></p>
                <p><input class="w3-input w3-border" type="email" placeholder="Email" name="Email" required></p>
                <p><input class="w3-input w3-border" type="text" placeholder="Subject" name="Subject" required></p>
                <p><textarea class="w3-input w3-border" placeholder="Message" name="Message" rows="5" required></textarea></p>
                <button type="submit" class="w3-button w3-block w3-black">Send</button>
            </form>
        </div>

        <div class="w3-col s4">
            <h4>About</h4>
            <p><a href="about.php">About us</a></p>
            <p><a href="<?php echo $settings['jobsearch_url']; ?>" target="_blank">We're hiring</a></p>
            <p><a href="<?php echo $settings['support_url']; ?>" target="_blank">Support</a></p>
            <p><a href="stores.php">Find store</a></p>
            <p><a href="shipping.php">Shipment</a></p>
            <p><a href="payment.php">Payment</a></p>
            <p><a href="giftcard.php">Gift card</a></p>
            <p><a href="return.php">Return</a></p>
            <p><a href="faqs.php">Help</a></p>
            <p><a href="https://stripe.com/privacy" target="_blank">STRIPE Privacy Policy</a></p>
            <p><a href="https://auctane.com/legal/privacy-policy/" target="_blank">ShipStaion Privacy Policy</a></p>
        </div>

        <div class="w3-col s4 w3-justify">
            <h4>Store</h4>
            <p><i class="fa fa-fw fa-map-marker"></i> <?php echo $settings['fa-map-marker']; ?></p>
            <p><i class="fa fa-fw fa-phone"></i> <?php echo $settings['fa-phone']; ?></p>
            <p><i class="fa fa-fw fa-envelope"></i> <?php echo $settings['fa-envelope']; ?></p>
            <h4>We accept</h4>
            <p><i class="fa fa-fw fa-cc-amex"></i> Amex</p>
            <p><i class="fa fa-fw fa-credit-card"></i> Credit Card</p>
            <br>
            <a href="<?php echo $settings['fa-facebook-official']; ?>" class="fa fa-facebook-official w3-hover-opacity w3-large"></a>
            <a href="<?php echo $settings['fa-instagram']; ?>" class="fa fa-instagram w3-hover-opacity w3-large"></a>
            <a href="<?php echo $settings['fa-snapchat']; ?>" class="fa fa-snapchat w3-hover-opacity w3-large"></a>
            <a href="<?php echo $settings['fa-pinterest-p']; ?>" class="fa fa-pinterest-p w3-hover-opacity w3-large"></a>
            <a href="<?php echo $settings['fa-twitter']; ?>" class="fa fa-twitter w3-hover-opacity w3-large"></a>
            <a href="<?php echo $settings['fa-linkedin']; ?>" class="fa fa-linkedin w3-hover-opacity w3-large"></a>
        </div>
    </div>
</footer>

<div class="w3-black w3-center w3-padding-24">Powered by <a href="https://www.w3schools.com/w3css/default.asp" title="W3.CSS" target="_blank" class="w3-hover-opacity">w3.css</a></div>

<!-- End page content -->
</div>

<!-- Newsletter Modal -->
<div id="newsletter" class="w3-modal">
    <div class="w3-modal-content w3-animate-zoom" style="padding:32px">
        <div class="w3-container w3-white w3-center">
            <form method="post">
                <i onclick="document.getElementById('newsletter').style.display='none'" class="fa fa-remove w3-right w3-button w3-transparent w3-xxlarge"></i>
                <h2 class="w3-wide">NEWSLETTER</h2>
                <p>Join our mailing list to receive updates on new arrivals and special offers.</p>
                <p><input class="w3-input w3-border" type="text" name="newsletter_email" placeholder="Enter e-mail"></p>
                <input type="hidden" name="action" value="newsletter_subscribe" />
                <button type="submit" class="w3-button w3-padding-large w3-red w3-margin-bottom" onclick="document.getElementById('newsletter').style.display='none'">Subscribe</button>
            </form>
        </div>
    </div>
</div>
