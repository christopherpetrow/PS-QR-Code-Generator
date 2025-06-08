# PS-QR-Code-Generator
A PrestaShop module which allows the customer message to be retrieved via a QR code.

## Installation

1. Copy the `psqrcode` folder into your PrestaShop `modules` directory.
2. In the PrestaShop back office, go to **Modules > Module Manager** and install **QR Code Message**.
3. When an order is validated a secure token is generated and stored in a new table. A QR code is then shown on the order confirmation page. Scanning the code opens a page that displays the customer's message.
4. The module will log each hook call in the PrestaShop logs under the `Psqrcode` object type for debugging.
5. A QR code is also displayed on the order details page in the back office for easy reference.

## Theme update

To capture a delivery note during checkout, edit your theme's
`templates/checkout/_partials/steps/shipping.tpl` and add the following
markup near the end of the shipping form (after the gift message block):

```smarty
  <div class="form-group mt-3">
            <label for="delivery_note">
              {l s='Delivery note' d='Shop.Theme.Checkout'}
            </label>
            <textarea
              id="delivery_note"
              name="delivery_note"
              rows="2"
              class="form-control"
              placeholder="{l s='Add a note for delivery (optional)' d='Shop.Theme.Checkout'}"
            ></textarea>
          </div>
```

An example file is included under `izpraticvete.eu/themes/warehouse/...` as a reference.
