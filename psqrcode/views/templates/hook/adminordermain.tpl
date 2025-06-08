<div class="card mb-4">
  <h3 class="card-header">{$customer_message_label}</h3>
  <div class="card-body">
        <img src="{$qr_url}" alt="QR code" />
        <p><a href="{$display_url|escape:'html'}">{l s='Open link' mod='psqrcode'}</a></p>
        {if isset($delivery_note) && $delivery_note}
            <p><strong>{l s='Delivery note:' mod='psqrcode'}</strong> {$delivery_note|escape:'html'}</p>
        {/if}
  </div>
</div>
