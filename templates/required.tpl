{if $error}
{if $required}
         <font color="red" size="1">*</font>
     {/if}
     <font color="red">{$label}</font>
{else}
{$label}
{if $required}
<font color="red" size="1">*</font>
{/if}
{/if}
