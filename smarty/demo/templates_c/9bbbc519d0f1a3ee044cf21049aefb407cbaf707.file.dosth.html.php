<?php /* Smarty version Smarty-3.1.19, created on 2015-01-22 18:43:31
         compiled from "/home/awen/product/app/views/xym/mobile/html/user/dosth.html" */ ?>
<?php /*%%SmartyHeaderCode:92040136254c0d4538b1722-53268425%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '9bbbc519d0f1a3ee044cf21049aefb407cbaf707' => 
    array (
      0 => '/home/awen/product/app/views/xym/mobile/html/user/dosth.html',
      1 => 1421923403,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '92040136254c0d4538b1722-53268425',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'res' => 0,
    'id' => 0,
    'job' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.19',
  'unifunc' => 'content_54c0d4538d26f6_52004194',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54c0d4538d26f6_52004194')) {function content_54c0d4538d26f6_52004194($_smarty_tpl) {?><html>
	<body>
		<img src="<?php echo $_smarty_tpl->tpl_vars['res']->value;?>
/images/head.png"></img>
		<p>名字:<?php echo $_smarty_tpl->tpl_vars['id']->value;?>
</p>
		<p>阵营:<?php echo $_smarty_tpl->tpl_vars['job']->value;?>
</p>
	</body>
</html>
<?php }} ?>
