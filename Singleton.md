# 单例模式 #
#### 所谓单例模式，即在应用程序中最多只有该类的一个实例存在，一旦创建，就会一直存在于内存中！####
#### 应用场景：
#### 单例设计模式常应用于数据库类设计，采用单例模式，只连接一次数据库，防止打开多个数据库连接。####
#### 一个单例类应具备以下特点：####
#### 单例类不能直接实例化创建，而是只能由类本身实例化。因此，要获得这样的限制效果，构造函数必须标记为private，从而防止类被实例化。####
#### 需要一个私有静态成员变量来保存类实例和公开一个能访问到实例的公开静态方法。####
#### 在PHP中，为了防止他人对单例类实例克隆，通常还为其提供一个空的私有__clone()方法####

## 用法 ##
    
    $config = [
    'host' => 'xx',
    'username' => 'xx',
    'password' => 'xxx',
    'dbname' => 'xxx'
    ];

    $a = Database::getInstance($config);

    $data = $a->table('config')
    	->where(['id'=>3])
    	->order(['create_time desc','id desc'])
    	->limit('3')
    	->colomn(['name','value'])
    	->update(['value'=>'ree','name'=>'tt']);

    $data = $a->table('config')
    	->where(['id'=>2])
    	->group('name')
    	->order(['create_time desc','id desc'])
    	->limit('3')
    	->colomn(['name','value'])
    	->select();
    
    $data = $a->table('config')
    	->where(['name'=>'565'])
    	->order(['create_time desc','id desc'])
    	->limit('3')
    	->colomn(['name','value'])
    	->delete();
    
    $data = $a->table('config')
    	->where(['name'=>'565'])
    	->order(['create_time desc','id desc'])
    	->limit('3')
    	->colomn(['name','value'])
    	->insert(['abc'=>343,'bad'=>4545,'io'=>4343]);

    
