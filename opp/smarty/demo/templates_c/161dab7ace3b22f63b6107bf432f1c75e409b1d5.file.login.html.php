<?php /* Smarty version Smarty-3.1.19, created on 2015-01-23 19:19:36
         compiled from "/home/awen/product/opp//views/xym/mobile/html/user/login.html" */ ?>
<?php /*%%SmartyHeaderCode:14247239654c22e483b3ec7-24263783%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '161dab7ace3b22f63b6107bf432f1c75e409b1d5' => 
    array (
      0 => '/home/awen/product/opp//views/xym/mobile/html/user/login.html',
      1 => 1422004177,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '14247239654c22e483b3ec7-24263783',
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
  'unifunc' => 'content_54c22e48544f90_13588039',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54c22e48544f90_13588039')) {function content_54c22e48544f90_13588039($_smarty_tpl) {?><html>
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
