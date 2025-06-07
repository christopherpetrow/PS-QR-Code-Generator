# PS-QR-Code-Generator
A PrestaShop module which allows the customer message to be retrieved via a QR code.

## Installation

1. Copy the `psqrcode` folder into your PrestaShop `modules` directory.
2. In the PrestaShop back office, go to **Modules > Module Manager** and install **QR Code Message**.
3. When an order is validated a secure token is generated and stored in a new table. A QR code is then shown on the order confirmation page. Scanning the code opens a page that displays the customer's message.
4. The module will log each hook call in the PrestaShop logs under the `Psqrcode` object type for debugging.
