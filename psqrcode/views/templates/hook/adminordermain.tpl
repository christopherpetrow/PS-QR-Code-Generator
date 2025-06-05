<div class="qr-message">
    <p>{l s='Customer message:' mod='psqrcode'}</p>
    <p>{$customer_message|escape:'html'}</p>
    <img src="{$qr_url}" alt="QR code" />
</div>
