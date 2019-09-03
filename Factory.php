<?php
/**
 * 工厂设计模式常用于根据输入参数的不同或者应用程序配置的不同来创建一种专门用来实例化并返回其对应的类的实例。
 * 使用场景：使用方法 new实例化类，每次实例化只需调用工厂类中的方法实例化即可。
 * 优点：由于一个类可能会在很多地方被实例化。当类名或参数发生变化时，工厂模式可简单快捷的在工厂类下的方法中 一次性修改，避免了一个个的去修改实例化的对象。
 * 我们举例子，假设矩形、圆都有同样的一个方法，那么我们用基类提供的API来创建实例时，通过传参数来自动创建对应的类的实例，他们都有获取周长和面积的功能。
 */
interface Shape
{
    // 计算面积
    function getArea();

    // 计算周长
    function getCircumference();
}

// 矩形
class Rectangle implements Shape
{
    private $_width;
    private $_height;

    public function __construct($width,$height)
    {
        $this->_width = $width;
        $this->_height = $height;
    }

    public function getArea()
    {
        // TODO: Implement getArea() method.
        return $this->_width * $this->_height;
    }

    public function getCircumference()
    {
        // TODO: Implement getCircumference() method.
        return 2*$this->_width + 2*$this->_height;
    }
}

// 原型
class Circle implements Shape
{
    private $_radius;

    function __construct($radius)
    {
        $this->_radius = $radius;
    }

    public function getArea()
    {
        // TODO: Implement getArea() method.
        return M_PI * pow($this->_radius,2);
    }

    public function getCircumference()
    {
        // TODO: Implement getCircumference() method.
        return 2* M_PI *$this->_radius;
    }
}

// 工厂类
class Factory
{
    public static function create()
    {
        switch (func_num_args()){
            case 1:
                return new Circle(func_get_args(0));
            case 2:
                return new Rectangle(func_get_args(0),func_get_args(1));
            default:
            break;
        }
    }
}

$a =Factory::create(5,5);
var_dump($a);