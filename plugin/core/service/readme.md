这里面写插件提供的服务
一个服务，一般由三个文件组成
- 服务具体实现者 这个类无需继承任何基类，只需要实现服务的具体方法
- 服务提供者，也可以理解为服务注册者，将服务实现者类注册进ThinkPHP容器中，方便进行调用
- 服务门面，为了在某些地方，不方便直接使用服务提供者的地方，能静态的访问服务实现者的方法，比如中间件中需要使用服务，那么就可能需要使用到服务门面

其中，以Service.php结尾的文件为服务提供者，服务提供者的类名全称会加入/config/service.php文件中，完成服务的注册

不移动到/app/common下了，负载均衡就全量复制到多个服务器