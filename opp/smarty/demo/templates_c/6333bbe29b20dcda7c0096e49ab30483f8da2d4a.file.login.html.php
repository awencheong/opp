<?php /* Smarty version Smarty-3.1.19, created on 2015-01-22 18:32:36
         compiled from "/home/awen/product/opp/views/xym/mobile/html/user/login.html" */ ?>
<?php /*%%SmartyHeaderCode:138297493154c0d1c4c713c6-25862310%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '6333bbe29b20dcda7c0096e49ab30483f8da2d4a' => 
    array (
      0 => '/home/awen/product/opp/views/xym/mobile/html/user/login.html',
      1 => 1421922678,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '138297493154c0d1c4c713c6-25862310',
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
  'unifunc' => 'content_54c0d1c4c92550_21081039',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54c0d1c4c92550_21081039')) {function content_54c0d1c4c92550_21081039($_smarty_tpl) {?><html>
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
