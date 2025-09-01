# ThinkAdmin 项目开发规则

## 1. 后端控制器开发规范

### 基本结构
```php
class ModuleName extends Controller
{
    /** @auth true @menu true */
    public function index() { return $this->fetch(); }
    
    /** @auth true */
    public function form() { return $this->fetch(); }
}
```

### 规则
1. 继承 `think\admin\Controller`
2. 每个方法添加 `@auth true`
3. 主要页面添加 `@menu true`
4. 使用 PascalCase 命名
5. 标准方法：index、form、remove

## 2. 模型文件开发规范

### 基本结构
```php
class ModelName extends Model
{
    protected $name = 'table_name';
    protected $autoWriteTimestamp = true;
    protected $type = ['status' => 'integer'];
}
```

### 规则
1. 继承 `think\admin\Model`
2. 定义 `$name` 属性
3. 启用自动时间戳
4. 定义字段类型转换
5. 使用 `mForm` 方法处理表单

## 3. 前端表格组件规范

### HTML结构
```html
<table id="DataTable" data-url="{:sysuri()}"></table>
```

### JavaScript配置
```javascript
$('#DataTable').layTable({
    height: 'full',
    cols: [[{field: 'id', title: 'ID'}]]
});
```

### QueryHelper后端处理
```php
public function index()
{
    $this->_query()->layTable(function($query){
        $query->like('field');
    });
}
```

### 规则
1. 表格ID使用驼峰命名
2. 使用 `{:sysuri()}` 配置URL
3. 高度设置 `height: 'full'`
4. 事件绑定先 `off` 再 `on`
5. 使用QueryHelper处理数据

## 4. 前端模板继承规范

### 完整异步页面 (full.html)
**用途**：登录页面、iframe页面
```html
{extend name="../../admin/view/full"}
{block name='content'}内容{/block}
{block name='script'}脚本{/block}
```

### 内容异步页面 (main.html)
**用途**：后台管理主要功能页面
```html
{extend name="../../admin/view/main"}
{block name="button"}按钮{/block}
{block name='content'}内容{/block}
```

### 弹层模板页面
**用途**：表单编辑、数据查看弹窗
```html
<form class="layui-form" data-auto="true">
    <!-- 表单内容 -->
</form>
```

### 规则
1. full.html：完整页面结构，有加载顺序要求
2. main.html：内容页面，无加载顺序限制
3. 弹层：仅包含内容，不含头部脚本
4. 使用requirejs加载第三方插件
5. 不建议升级LayUI版本

**注意**：严格按照以上标准开发，确保代码规范统一。

