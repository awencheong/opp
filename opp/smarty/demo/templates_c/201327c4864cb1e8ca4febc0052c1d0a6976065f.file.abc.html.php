<?php /* Smarty version Smarty-3.1.19, created on 2015-01-22 20:13:32
         compiled from "/home/awen/product/opp/views/xym/mobile/html/user/abc.html" */ ?>
<?php /*%%SmartyHeaderCode:67579503454c0e90e0db384-48523478%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '201327c4864cb1e8ca4febc0052c1d0a6976065f' => 
    array (
      0 => '/home/awen/product/opp/views/xym/mobile/html/user/abc.html',
      1 => 1421928809,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '67579503454c0e90e0db384-48523478',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.19',
  'unifunc' => 'content_54c0e90e11d342_04191266',
  'variables' => 
  array (
    'res' => 0,
    'id' => 0,
    'job' => 0,
    'another' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54c0e90e11d342_04191266')) {function content_54c0e90e11d342_04191266($_smarty_tpl) {?><html>
	<body>
		<h1> this is from abc </h1>
		<img src="<?php echo $_smarty_tpl->tpl_vars['res']->value;?>
/images/head.png"></img>
		<p>名字:<?php echo $_smarty_tpl->tpl_vars['id']->value;?>
</p>
		<p>阵营:<?php echo $_smarty_tpl->tpl_vars['job']->value;?>
</p>
		<p>职业:<?php echo $_smarty_tpl->tpl_vars['another']->value['data']['job'];?>
</p>
		<p>升星上限:<?php echo $_smarty_tpl->tpl_vars['another']->value['data']['max_star_level'];?>
</p>
	</body>
</html>
<?php }} ?>
