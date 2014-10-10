      <H2>Устройство &#8470;{$device.id}</H2>
      <H3>Информация об устройстве</H3>
Клиент: {$device.client}<br>
Устройство: {$device.manufacturer} {$device.model} {$device.serial}<br>
MAC: {$device.mac}<br>
IP сети: {$device.ip}<br>
Номера: {$device.numbers}<br>
<br>{if access('routers_devices','edit')}
<a href='{$LINK_START}module=routers&action=d_edit&id={$device.id}'>Редактировать</a><br>
{/if}
