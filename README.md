# PS-QR-Code-Generator
A PrestaShop module which allows the user to create a message stored into a QR code. 

## Installation

1. Copy the `psqrcode` folder into your PrestaShop `modules` directory.
2. In the PrestaShop back office, go to **Modules > Module Manager** and install **QR Code Message**.
3. After a customer leaves a message in the order comment section and completes the order, a QR code containing their message will be displayed on the order confirmation page.
4. The same QR code is visible to employees on the order detail page in the Back Office via the `displayAdminOrderMain` hook.
5. The module will log each hook call in the PrestaShop logs under the `Psqrcode` object type for debugging.
