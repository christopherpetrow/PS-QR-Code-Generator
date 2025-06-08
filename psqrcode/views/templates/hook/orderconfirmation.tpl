<div class="qr-message">
    <p>{l s='Scan the QR code to view your message.' mod='psqrcode'}</p>
    <img src="{$qr_url}" alt="QR code" />
    <p><a href="{$display_url|escape:'html'}">{l s='Open link' mod='psqrcode'}</a></p>
    {if isset($delivery_note) && $delivery_note}
      <p>{l s='Delivery note:' mod='psqrcode'} {$delivery_note|escape:'html'}</p>
    {/if}
</div>
