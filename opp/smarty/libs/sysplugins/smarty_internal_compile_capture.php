<?php
/**
 * Smarty Internal Plugin Compile Capture
 * Compiles the {capture} tag
 *
 * @package    Smarty
 * @subpackage Compiler
 * @author     Uwe Tews
 */

/**
 * Smarty Internal Plugin Compile Capture Class
 *
 * @package    Smarty
 * @subpackage Compiler
 */
class Smarty_Internal_Compile_Capture extends Smarty_Internal_CompileBase
{
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smarty_Internal_CompileBase
     */
    public $shorttag_order = array('name');
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smarty_Internal_CompileBase
     */
    public $optional_attributes = array('name', 'assign', 'oppend');

    /**
     * Compiles code for the {capture} tag
     *
     * @param  array  $args     array with attributes from parser
     * @param  object $compiler compiler object
     *
     * @return string compiled code
     */
    public function compile($args, $compiler)
    {
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);

        $buffer = isset($_attr['name']) ? $_attr['name'] : "'default'";
        $assign = isset($_attr['assign']) ? $_attr['assign'] : 'null';
        $oppend = isset($_attr['oppend']) ? $_attr['oppend'] : 'null';

        $compiler->_capture_stack[0][] = array($buffer, $assign, $oppend, $compiler->nocache);
        // maybe nocache because of nocache variables
        $compiler->nocache = $compiler->nocache | $compiler->tag_nocache;
        $_output = "<?php \$_smarty_tpl->_capture_stack[0][] = array($buffer, $assign, $oppend); ob_start(); ?>";

        return $_output;
    }
}

/**
 * Smarty Internal Plugin Compile Captureclose Class
 *
 * @package    Smarty
 * @subpackage Compiler
 */
class Smarty_Internal_Compile_CaptureClose extends Smarty_Internal_CompileBase
{
    /**
     * Compiles code for the {/capture} tag
     *
     * @param  array  $args     array with attributes from parser
     * @param  object $compiler compiler object
     *
     * @return string compiled code
     */
    public function compile($args, $compiler)
    {
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);
        // must endblock be nocache?
        if ($compiler->nocache) {
            $compiler->tag_nocache = true;
        }

        list($buffer, $assign, $oppend, $compiler->nocache) = array_pop($compiler->_capture_stack[0]);

        $_output = "<?php list(\$_capture_buffer, \$_capture_assign, \$_capture_oppend) = array_pop(\$_smarty_tpl->_capture_stack[0]);\n";
        $_output .= "if (!empty(\$_capture_buffer)) {\n";
        $_output .= " if (isset(\$_capture_assign)) \$_smarty_tpl->assign(\$_capture_assign, ob_get_contents());\n";
        $_output .= " if (isset( \$_capture_oppend)) \$_smarty_tpl->oppend( \$_capture_oppend, ob_get_contents());\n";
        $_output .= " Smarty::\$_smarty_vars['capture'][\$_capture_buffer]=ob_get_clean();\n";
        $_output .= "} else \$_smarty_tpl->capture_error();?>";

        return $_output;
    }
}
