-- Schema: log

-- DROP SCHEMA log;

CREATE SCHEMA log
  AUTHORIZATION postgres;

-- Schema: system

-- DROP SCHEMA system;

CREATE SCHEMA system
  AUTHORIZATION postgres;

-- Table: log.operation_log

-- DROP TABLE log.operation_log;

CREATE TABLE log.operation_log
(
  lid serial NOT NULL,
  uid integer,
  username character(20),
  message character varying(200),
  itemid character varying(100),
  created_at timestamp without time zone,
  CONSTRAINT operation_log_pkey PRIMARY KEY (lid)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE log.operation_log
  OWNER TO postgres;
COMMENT ON TABLE log.operation_log
  IS '后台操作日志';

-- Index: log.operation_log_uid_idx

-- DROP INDEX log.operation_log_uid_idx;

CREATE INDEX operation_log_uid_idx
  ON log.operation_log
  USING btree
  (uid);


-- Table: log.api_time_analysis

-- DROP TABLE log.api_time_analysis;

CREATE TABLE log.api_time_analysis
(
  lid serial NOT NULL,
  pid integer, -- 项目ID
  uri character varying(300), -- 接口URL
  req_y integer, -- 点击次数（昨）
  req_t integer, -- 请求次数（今）
  time_y integer, -- 平均耗时（昨）
  time_t integer, -- 平均耗时（今）
  dateline date,
  time_max integer, -- 最大耗时
  time_min integer, -- 最小耗时
  CONSTRAINT api_time_analysis_pkey PRIMARY KEY (lid)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE log.api_time_analysis
  OWNER TO postgres;
COMMENT ON TABLE log.api_time_analysis
  IS 'url耗时分析';
COMMENT ON COLUMN log.api_time_analysis.pid IS '项目ID';
COMMENT ON COLUMN log.api_time_analysis.uri IS '接口URL';
COMMENT ON COLUMN log.api_time_analysis.req_y IS '点击次数（昨）';
COMMENT ON COLUMN log.api_time_analysis.req_t IS '请求次数（今）';
COMMENT ON COLUMN log.api_time_analysis.time_y IS '平均耗时（昨）';
COMMENT ON COLUMN log.api_time_analysis.time_t IS '平均耗时（今）';
COMMENT ON COLUMN log.api_time_analysis.time_max IS '最大耗时';
COMMENT ON COLUMN log.api_time_analysis.time_min IS '最小耗时';


-- Index: log.api_time_analysis_pid_idx

-- DROP INDEX log.api_time_analysis_pid_idx;

CREATE INDEX api_time_analysis_pid_idx
  ON log.api_time_analysis
  USING btree
  (pid);



  -- Table: log.api_time_log

-- DROP TABLE log.api_time_log;

CREATE TABLE log.api_time_log
(
  lid serial NOT NULL,
  type character varying(10), -- 类型：php,service,db,redis
  pid integer, -- 项目ID
  uri character varying(300),
  "time" integer, -- 耗时（ms）
  dateline timestamp without time zone,
  xhprof_id character(32),
  CONSTRAINT api_time_log_pkey PRIMARY KEY (lid)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE log.api_time_log
  OWNER TO postgres;
COMMENT ON TABLE log.api_time_log
  IS '项目耗时日志表';
COMMENT ON COLUMN log.api_time_log.type IS '类型：php,service,db,redis';
COMMENT ON COLUMN log.api_time_log.pid IS '项目ID';
COMMENT ON COLUMN log.api_time_log."time" IS '耗时（ms）';


-- Index: log.api_time_log_pid_uri_idx

-- DROP INDEX log.api_time_log_pid_uri_idx;

CREATE INDEX api_time_log_pid_uri_idx
  ON log.api_time_log
  USING btree
  (pid, uri COLLATE pg_catalog."default");




  -- Table: log.project_time_analysis

-- DROP TABLE log.project_time_analysis;

CREATE TABLE log.project_time_analysis
(
  lid serial NOT NULL,
  pid integer, -- 项目ID
  req_y integer, -- 点击次数（昨）
  req_t integer, -- 请求次数（今）
  time_y integer, -- 平均耗时（昨）
  time_t integer, -- 平均耗时（今）
  dateline date,
  time_max integer,
  time_min integer, -- 项目最小耗时
  CONSTRAINT project_time_analysis_pkey PRIMARY KEY (lid)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE log.project_time_analysis
  OWNER TO postgres;
COMMENT ON TABLE log.project_time_analysis
  IS 'url耗时分析';
COMMENT ON COLUMN log.project_time_analysis.pid IS '项目ID';
COMMENT ON COLUMN log.project_time_analysis.req_y IS '点击次数（昨）';
COMMENT ON COLUMN log.project_time_analysis.req_t IS '请求次数（今）';
COMMENT ON COLUMN log.project_time_analysis.time_y IS '平均耗时（昨）';
COMMENT ON COLUMN log.project_time_analysis.time_t IS '平均耗时（今）';
COMMENT ON COLUMN log.project_time_analysis.time_min IS '项目最小耗时';


-- Index: log.project_time_analysis_pid_idx

-- DROP INDEX log.project_time_analysis_pid_idx;

CREATE INDEX project_time_analysis_pid_idx
  ON log.project_time_analysis
  USING btree
  (pid);


  -- Table: system.module

-- DROP TABLE system.module;

CREATE TABLE system.module
(
  mid serial NOT NULL,
  parent_id integer, -- 上级模块
  mname character varying(20), -- 模块名称
  mtag character varying(20), -- 模块标识
  order_num integer, -- 排序：升序
  status smallint DEFAULT 1, -- 状态：1 显示，2隐藏
  created_at timestamp without time zone,
  updated_at timestamp without time zone,
  icon character varying(20), -- 图标
  CONSTRAINT module_pkey PRIMARY KEY (mid),
  CONSTRAINT module_mtag_key UNIQUE (mtag)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE system.module
  OWNER TO postgres;
COMMENT ON TABLE system.module
  IS '模块';
COMMENT ON COLUMN system.module.parent_id IS '上级模块';
COMMENT ON COLUMN system.module.mname IS '模块名称';
COMMENT ON COLUMN system.module.mtag IS '模块标识';
COMMENT ON COLUMN system.module.order_num IS '排序：升序';
COMMENT ON COLUMN system.module.status IS '状态：1 显示，2隐藏';
COMMENT ON COLUMN system.module.icon IS '图标';


-- Table: system.project

-- DROP TABLE system.project;

CREATE TABLE system.project
(
  pid serial NOT NULL,
  uid integer,
  pname character varying(30), -- 项目名称
  ptag character varying(100), -- 项目标识
  created_at timestamp without time zone,
  updated_at timestamp without time zone,
  CONSTRAINT project_pkey PRIMARY KEY (pid),
  CONSTRAINT project_ptag_key UNIQUE (ptag)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE system.project
  OWNER TO postgres;
COMMENT ON TABLE system.project
  IS '项目';
COMMENT ON COLUMN system.project.pname IS '项目名称';
COMMENT ON COLUMN system.project.ptag IS '项目标识';


-- Index: system.project_ptag_idx

-- DROP INDEX system.project_ptag_idx;

CREATE INDEX project_ptag_idx
  ON system.project
  USING btree
  (ptag COLLATE pg_catalog."default");

-- Index: system.project_uid_idx

-- DROP INDEX system.project_uid_idx;

CREATE INDEX project_uid_idx
  ON system.project
  USING btree
  (uid);


  -- Table: system.role

-- DROP TABLE system.role;

CREATE TABLE system.role
(
  rid serial NOT NULL,
  rname character varying(20), -- 角色名
  created_at timestamp without time zone,
  mids character varying(300), -- 模块ID，多个用逗号分隔
  CONSTRAINT role_pkey PRIMARY KEY (rid)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE system.role
  OWNER TO postgres;
COMMENT ON TABLE system.role
  IS '角色';
COMMENT ON COLUMN system.role.rname IS '角色名';
COMMENT ON COLUMN system.role.mids IS '模块ID，多个用逗号分隔';


-- Table: system.role_module

-- DROP TABLE system.role_module;

CREATE TABLE system.role_module
(
  rmid serial NOT NULL,
  rid integer,
  mid integer,
  CONSTRAINT role_module_pkey PRIMARY KEY (rmid)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE system.role_module
  OWNER TO postgres;
COMMENT ON TABLE system.role_module
  IS '角色模块关系表';

-- Index: system.role_module_rid_idx

-- DROP INDEX system.role_module_rid_idx;

CREATE INDEX role_module_rid_idx
  ON system.role_module
  USING btree
  (rid);


  -- Table: system."user"

-- DROP TABLE system."user";

CREATE TABLE system."user"
(
  uid serial NOT NULL,
  username character varying(30),
  role integer,
  mobile character(11),
  password character(32),
  salt integer,
  status smallint,
  created_at timestamp without time zone,
  update_at timestamp without time zone,
  CONSTRAINT users_pkey PRIMARY KEY (uid)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE system."user"
  OWNER TO postgres;



  -- Table: system.monit

-- DROP TABLE system.monit;

CREATE TABLE system.monit
(
  pid integer NOT NULL, -- 项目ID
  alarm_mode character varying(10), -- 报警方式（多项用,分隔）：1 邮件，2 短信，3 电话
  alarm_email character varying(100), -- 报警邮件
  alarm_mobile character varying(11), -- 报警手机
  slow_alarm smallint DEFAULT 1, -- 慢报警:1开 2关
  slow_time smallint DEFAULT 1000, -- 慢报警阀值(ms)，默认为1000
  error_alarm smallint DEFAULT 1, -- 错误报警：1开，2关
  error_status smallint DEFAULT 500, -- 错误状态码，默认为500
  CONSTRAINT monit_pkey PRIMARY KEY (pid)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE system.monit
  OWNER TO postgres;
COMMENT ON TABLE system.monit
  IS '监控设置表';
COMMENT ON COLUMN system.monit.pid IS '项目ID';
COMMENT ON COLUMN system.monit.alarm_mode IS '报警方式（多项用,分隔）：1 邮件，2 短信，3 电话';
COMMENT ON COLUMN system.monit.alarm_email IS '报警邮件';
COMMENT ON COLUMN system.monit.alarm_mobile IS '报警手机';
COMMENT ON COLUMN system.monit.slow_alarm IS '慢报警:1开 2关';
COMMENT ON COLUMN system.monit.slow_time IS '慢报警阀值(ms)，默认为1000';
COMMENT ON COLUMN system.monit.error_alarm IS '错误报警：1开，2关';
COMMENT ON COLUMN system.monit.error_status IS '错误状态码，默认为500';


-- Table: admin.api_log

-- DROP TABLE admin.api_log;

CREATE TABLE admin.api_log
(
  lid serial NOT NULL,
  pid integer, -- 项目ID
  ptag character varying(100),
  uri character varying(300),
  method character varying(10), -- 请求方式
  http_code integer, -- HTTP状态码
  wt integer, -- 请求耗时（us）
  cpu integer, -- CPU 时间（us）
  mu integer, -- 内存使用量(Byte)
  pmu integer, -- 内存使用量峰值（Byte）
  req_ip character varying(50), -- 请求者IP
  req_at timestamp without time zone, -- 请求时间
  xhprof_id character(32),
  created_at timestamp without time zone,
  CONSTRAINT api_log_pkey PRIMARY KEY (lid)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE admin.api_log
  OWNER TO postgres;
COMMENT ON TABLE admin.api_log
  IS '项目耗时日志表';
COMMENT ON COLUMN admin.api_log.pid IS '项目ID';
COMMENT ON COLUMN admin.api_log.method IS '请求方式';
COMMENT ON COLUMN admin.api_log.http_code IS 'HTTP状态码';
COMMENT ON COLUMN admin.api_log.wt IS '请求耗时（us）';
COMMENT ON COLUMN admin.api_log.cpu IS 'CPU 时间（us）';
COMMENT ON COLUMN admin.api_log.mu IS '内存使用量(Byte)';
COMMENT ON COLUMN admin.api_log.pmu IS '内存使用量峰值（Byte）';
COMMENT ON COLUMN admin.api_log.req_ip IS '请求者IP';
COMMENT ON COLUMN admin.api_log.req_at IS '请求时间';


-- Index: admin.api_log_ptag_idx

-- DROP INDEX admin.api_log_ptag_idx;

CREATE INDEX api_log_ptag_idx
  ON admin.api_log
  USING btree
  (ptag COLLATE pg_catalog."default");

-- Index: admin.api_log_xhprof_id_idx

-- DROP INDEX admin.api_log_xhprof_id_idx;

CREATE INDEX api_log_xhprof_id_idx
  ON admin.api_log
  USING btree
  (xhprof_id COLLATE pg_catalog."default");
  
  
  
  -- Table: admin.func_log

-- DROP TABLE admin.func_log;

CREATE TABLE admin.func_log
(
  lid serial NOT NULL,
  func_name character varying(300), -- 当前调用函数
  parent_func character varying(300), -- 上级调用函数
  ct integer, -- 调用次数
  wt integer, -- 请求耗时（us）
  cpu integer, -- CPU 时间（us）
  mu integer, -- 内存使用量（Byte）
  pmu integer, -- 内存使用量峰值（Byte）
  xhprof_id character(32),
  created_at timestamp without time zone,
  CONSTRAINT func_log_pkey PRIMARY KEY (lid)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE admin.func_log
  OWNER TO postgres;
COMMENT ON TABLE admin.func_log
  IS '项目耗时日志表';
COMMENT ON COLUMN admin.func_log.func_name IS '当前调用函数';
COMMENT ON COLUMN admin.func_log.parent_func IS '上级调用函数';
COMMENT ON COLUMN admin.func_log.ct IS '调用次数';
COMMENT ON COLUMN admin.func_log.wt IS '请求耗时（us）';
COMMENT ON COLUMN admin.func_log.cpu IS 'CPU 时间（us）';
COMMENT ON COLUMN admin.func_log.mu IS '内存使用量（Byte）';
COMMENT ON COLUMN admin.func_log.pmu IS '内存使用量峰值（Byte）';


-- Index: admin.func_log_parent_func_idx

-- DROP INDEX admin.func_log_parent_func_idx;

CREATE INDEX func_log_parent_func_idx
  ON admin.func_log
  USING btree
  (parent_func COLLATE pg_catalog."default");

-- Index: admin.func_log_xhprof_id_idx

-- DROP INDEX admin.func_log_xhprof_id_idx;

CREATE INDEX func_log_xhprof_id_idx
  ON admin.func_log
  USING btree
  (xhprof_id COLLATE pg_catalog."default");






