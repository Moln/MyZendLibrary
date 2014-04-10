<?php
namespace My\View\Helper;
use Zend_View_Helper_HtmlElement;

/**
 * 表格视图
 *
 * @author maomao
 * @version $Id: Table.php 1348 2014-04-03 18:24:19Z maomao $
 */
class Table extends Zend_View_Helper_HtmlElement
{
    /**
     * Table caption
     *
     * @var string
     */
    protected $caption = array();

    /**
     * Table columns
     *
     * @var array
     */
    protected $columns = array();

    protected $columnsKey = array();

    /**
     * Rows
     *
     * @var array
     */
    protected $rows = array();

    /**
     * Footer content
     *
     * @var string
     */
    protected $footer;

    /**
     * Table attributes
     *
     * @var array
     */
    protected $attributes = array(
        'cellspacing' => '0',
        'border'      => '1'
    );

    protected $rowsAttributes = array();

    /**
     *
     * @param array $columns 数组格式:
     *     键                      类型              译
     * - title      string   标题
     * - attrs      array    字段样式
     * - children   array    子字段
     * - valueMap   array    数据过滤器: 键值对替换
     * - template   string   数据过滤器: 模板替换
     * - function   function 数据过滤器: 自定义函数处理,参数1: $row;参数2: $key;
     * - viewHelper string   数据过滤器: 视图助手
     * - value      string   数据值
     *
     * @param array $rows
     * @param array $attrs
     * @return Table
     */
    public function table($columns = null, $rows = null, array $attrs = null)
    {
        if ($columns) {
            $this->setColumns($columns);
        }
        if ($rows) {
            $this->setRows($rows);
        }
        if ($attrs) {
            $this->setAttributes($attrs);
        }

        return $this;
    }

    /**
     * Set table caption
     *
     * @param string $caption
     * @return Table
     */
    public function setCaption($caption)
    {
        $this->caption = $caption;
        return $this;
    }

    /**
     * Set table columns
     *
     * @param array $columns
     *
     * @return Table
     */
    public function setColumns(array $columns)
    {
        $this->columns = $columns;
        return $this;
    }

    /**
     * Add row
     *
     * @param $row
     *
     * @internal param array $cols
     * @return Table
     */
    public function addRow($row)
    {
        if (is_array($row)) {
            array_push($this->rows, $row);
        } elseif (is_object($row) && method_exists($row, 'toArray')) {
            array_push($this->rows, $row->toArray());
        }

        return $this;
    }

    public function addColumn($column)
    {
        array_push($this->columns, $column);
        return $this;
    }

    /**
     *
     *
     * @param array|\Zend_Db_Table_Rowset $rows
     *
     * @throws \RuntimeException
     * @return Table
     */
    public function setRows($rows)
    {
        if (is_object($rows) && method_exists($rows, 'toArray')) {
            $rows = $rows->toArray();
        } else if (!is_array($rows)) {
            throw new \RuntimeException('错误类型(' . gettype($rows) . ')');
        }

        $this->rows = $rows;
        return $this;
    }

    /**
     * Set footer content
     *
     * @param string $footer
     * @return Table
     */
    public function setFooter($footer)
    {
        $this->footer = $footer;
        return $this;
    }

    /**
     * Set table Attributes
     * @param array $attribs
     * @return Table
     */
    public function setAttributes(array $attribs)
    {
        $this->attributes += $attribs;
        return $this;
    }

    public function setRowAttributes($index, array $attribs)
    {
        $this->rowsAttributes[$index] = $attribs;
        return $this;
    }

    public function setRowsAttributes(array $attribs)
    {
        $this->rowsAttributes = array_fill(0, count($this->rows)-1, $attribs);
        return $this;
    }

    /**
     * Render table
     *
     * @return string
     */
    public function __toString()
    {
        $html = '<table ' . $this->_htmlAttribs($this->attributes) . '>';
        if ($this->caption) {
            $html .= '<caption>' . $this->view->escape($this->caption) . '</caption>';
        }

        if (!empty($this->columns)) {
            $html .= $this->createHeader();
        }

        if (!empty($this->rows)) {
            $html .= $this->createRows() ;
        }

        if (!empty($this->footer)) {
            $html .= $this->createFooter();
        }
        $html .= '</table>';

        return $html;
    }

    public function toArray()
    {
        $rows       = $this->rows;
        $columnsKey = $this->columnsKey;
        if (!empty($columnsKey)) {

            //先对空的进行过滤, 下一个循环会快一点
            foreach ($columnsKey as $key => $filters) {
                if (empty($filters)) {
                    unset($columnsKey[$key]);
                }
            }

            foreach ($rows as &$row) {
                foreach ($columnsKey as $key => $filters) {
                    $row[$key] = $this->filterRow($filters, $row, $key);
                }
            }
        }

        return $rows;
    }

    protected function loopCreateColumns(&$columns, $index = 0, &$rowspan = array(1))
    {
        if (isset($columns['children'])) {
            if ($index-1 >= 0) {
                if (isset($rowspan[$index-1])) {
                    $rowspan[$index-1]++;
                } else {
                    $rowspan[$index-1] = 1;
                }
            }
            foreach ($columns['children'] as $key => &$column) {
                if (!is_array($column)) {
                    $column = array('title' => $column);
                }
                if (is_string($key)) {
                    $column['value'] = $key;
                }
                $this->columnRows[$index][] = &$column;
                if (!isset($column['children'])) {
                    $column['attrs']['rowspan'] = &$rowspan[$index];
                    $columnKey = $column;
                    unset($columnKey['title'], $columnKey['attrs']);
                    $this->columnsKey[] = $columnKey;
                } else {
                    $column['attrs']['colspan'] = count($column['children']);
                    $columns['attrs']['colspan'] += $column['attrs']['colspan']-1;
                }

                $this->loopCreateColumns($column, $index+1, $rowspan);
                unset($column['children']);

            }
        }
        return $index;
    }

    protected $columnRows;

    public function createColumns()
    {
        if (!$this->columnRows) {
            $this->columnsKey = array();
            $columns = array('children' => $this->columns, 'attrs' => array('colspan' => 0));
            $this->loopCreateColumns($columns);
        }

        return $this;
    }

    public function getColumnsKey()
    {
        return $this->columnsKey;
    }

    protected function createHeader()
    {
        $this->createColumns();
        $html = '<thead>';
        foreach ($this->columnRows as $rows) {
            $html .= '<tr>';
            foreach ($rows as $row) {
                if (empty($row['attrs']['rowspan']) || $row['attrs']['rowspan'] == 1) {
                    unset($row['attrs']['rowspan']);
                }
                $html .= '<th' . $this->_htmlAttribs($row['attrs']) . '>'
                       . $row['title'] . '</th>';
            }
            $html .= '</tr>';
        }
        $html .= '</thead>';

        return $html;
    }

    protected function createRows()
    {
        $html = '<tbody>';
        if (!empty($this->rows)) {
            if (empty($this->columnsKey)) {
                foreach ($this->rows as $index => $row) {
                    $html .= '<tr '
                           . (isset($this->rowsAttributes[$index])
                              ? $this->_htmlAttribs($this->rowsAttributes[$index])
                              : '' )
                           . '>';
                    foreach ($row as $value) {
                        $html .= "<td>$value</td>";
                    }
                    $html .= '</tr>';
                }
            } else {
                foreach ($this->rows as $index => $row) {
                    $html .= '<tr '
                           . (isset($this->rowsAttributes[$index])
                              ? $this->_htmlAttribs($this->rowsAttributes[$index])
                              : '' )
                           . '>';
                    foreach ($this->columnsKey as $filters) {
                        $tdAttrs = array();
                        $render = $this->filterRow($filters, $row, $tdAttrs);

                        $html .= '<td ' . (empty($tdAttrs) ? '' : $this->_htmlAttribs($tdAttrs));
                        $html .= '>';
                        $html .= $render;
                        $html .= '</td>';
                    }
                    $html .= '</tr>';
                }
            }
        }
        $html .= '</tbody>';
        return $html;
    }

    /**
     * 过滤键定义:
     * - valueMap   array    键值对替换
     * - template   string   模板替换
     * - function   function 自定义函数处理,参数1: $row;参数2: $key;
     * - viewHelper string   视图助手
     *
     * @param array $filters
     * @param array $row
     * @param array $tdAttrs
     * @return string
     */
    protected function filterRow($filters, $row, &$tdAttrs = array())
    {
        if (isset($filters['value'])) {
            $key = $filters['value'];
            $value = $row[$key];
            unset($filters['value']);
        } else {
            $key = $value = null;
        }

        foreach ($filters as $filter => $data) {
            switch ($filter) {
                case 'valueMap':
                    if (isset($data[$row[$key]])) {
                        $value = $data[$row[$key]];
                    }
                    break;
                case 'template':
                    $value = $this->template($data, $row);
                    break;
                case 'function':
                    $value = $data($row, $tdAttrs);
                    break;
                case 'viewHelper':
                    $value = $this->view->{$filter}($row[$key], $row);
                    break;
                default:
                    break;
            }
        }

        return $value;
    }

    protected function createFooter()
    {
        $html = '<tfoot><tr>';
        foreach ($this->footer as $cell) {
            $html .= '<td>' . $cell . '</td>';
        }
        $html .= '</tr></tfoot>';

        return $html;
    }

    protected function template($string, $row)
    {

        return preg_replace_callback(
            '/\{([^\{\}]+)\}/',
            function ($m) use ($row) {
                extract($row);
                return stripslashes(eval('return ' . $m[1] . ';'));
            },
            (string) $string
        );
    }
}