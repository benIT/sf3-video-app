<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\Intl\Exception\NotImplementedException;

/**
 * LTI tables installation
 * sql table instructions came from https://github.com/IMSGlobal/LTI-Tool-Provider-Library-PHP/wiki/Installation#database-tables
 * pgsql table instructions have been generated by the awesome tool: py-mysql2pgsql
 */
class Version20180118101427 extends AbstractMigration
{
    /**
     * LTI provider tool integration
     */
    public function up(Schema $schema)
    {
        $dbPlatform = $this->connection->getDatabasePlatform()->getName();
        $this->abortIf(!in_array($dbPlatform, ['mysql', 'postgresql']), 'Migration can only be executed safely on \'mysql\' or \'postgresql\'.');
        if ($dbPlatform === 'mysql') {
            $this->addSql('
            CREATE TABLE lti2_consumer (
  consumer_pk      INT(11)       NOT NULL AUTO_INCREMENT,
  name             VARCHAR(50)   NOT NULL,
  consumer_key256  VARCHAR(256)  NOT NULL,
  consumer_key     TEXT                   DEFAULT NULL,
  secret           VARCHAR(1024) NOT NULL,
  lti_version      VARCHAR(10)            DEFAULT NULL,
  consumer_name    VARCHAR(255)           DEFAULT NULL,
  consumer_version VARCHAR(255)           DEFAULT NULL,
  consumer_guid    VARCHAR(1024)          DEFAULT NULL,
  profile          TEXT                   DEFAULT NULL,
  tool_proxy       TEXT                   DEFAULT NULL,
  settings         TEXT                   DEFAULT NULL,
  protected        TINYINT(1)    NOT NULL,
  enabled          TINYINT(1)    NOT NULL,
  enable_from      DATETIME               DEFAULT NULL,
  enable_until     DATETIME               DEFAULT NULL,
  last_access      DATE                   DEFAULT NULL,
  created          DATETIME      NOT NULL,
  updated          DATETIME      NOT NULL,
  PRIMARY KEY (consumer_pk)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = latin1;

ALTER TABLE lti2_consumer
  ADD UNIQUE INDEX lti2_consumer_consumer_key_UNIQUE (consumer_key256 ASC);

CREATE TABLE lti2_tool_proxy (
  tool_proxy_pk INT(11)     NOT NULL AUTO_INCREMENT,
  tool_proxy_id VARCHAR(32) NOT NULL,
  consumer_pk   INT(11)     NOT NULL,
  tool_proxy    TEXT        NOT NULL,
  created       DATETIME    NOT NULL,
  updated       DATETIME    NOT NULL,
  PRIMARY KEY (tool_proxy_pk)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = latin1;

ALTER TABLE lti2_tool_proxy
  ADD CONSTRAINT lti2_tool_proxy_lti2_consumer_FK1 FOREIGN KEY (consumer_pk)
REFERENCES lti2_consumer (consumer_pk);

ALTER TABLE lti2_tool_proxy
  ADD INDEX lti2_tool_proxy_consumer_id_IDX (consumer_pk ASC);

ALTER TABLE lti2_tool_proxy
  ADD UNIQUE INDEX lti2_tool_proxy_tool_proxy_id_UNIQUE (tool_proxy_id ASC);

CREATE TABLE lti2_nonce (
  consumer_pk INT(11)     NOT NULL,
  value       VARCHAR(32) NOT NULL,
  expires     DATETIME    NOT NULL,
  PRIMARY KEY (consumer_pk, value)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = latin1;

ALTER TABLE lti2_nonce
  ADD CONSTRAINT lti2_nonce_lti2_consumer_FK1 FOREIGN KEY (consumer_pk)
REFERENCES lti2_consumer (consumer_pk);

CREATE TABLE lti2_context (
  context_pk     INT(11)      NOT NULL AUTO_INCREMENT,
  consumer_pk    INT(11)      NOT NULL,
  lti_context_id VARCHAR(255) NOT NULL,
  settings       TEXT                  DEFAULT NULL,
  created        DATETIME     NOT NULL,
  updated        DATETIME     NOT NULL,
  PRIMARY KEY (context_pk)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = latin1;

ALTER TABLE lti2_context
  ADD CONSTRAINT lti2_context_lti2_consumer_FK1 FOREIGN KEY (consumer_pk)
REFERENCES lti2_consumer (consumer_pk);

ALTER TABLE lti2_context
  ADD INDEX lti2_context_consumer_id_IDX (consumer_pk ASC);

CREATE TABLE lti2_resource_link (
  resource_link_pk         INT(11)    AUTO_INCREMENT,
  context_pk               INT(11)    DEFAULT NULL,
  consumer_pk              INT(11)    DEFAULT NULL,
  lti_resource_link_id     VARCHAR(255) NOT NULL,
  settings                 TEXT,
  primary_resource_link_pk INT(11)    DEFAULT NULL,
  share_approved           TINYINT(1) DEFAULT NULL,
  created                  DATETIME     NOT NULL,
  updated                  DATETIME     NOT NULL,
  PRIMARY KEY (resource_link_pk)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = latin1;

ALTER TABLE lti2_resource_link
  ADD CONSTRAINT lti2_resource_link_lti2_context_FK1 FOREIGN KEY (context_pk)
REFERENCES lti2_context (context_pk);

ALTER TABLE lti2_resource_link
  ADD CONSTRAINT lti2_resource_link_lti2_resource_link_FK1 FOREIGN KEY (primary_resource_link_pk)
REFERENCES lti2_resource_link (resource_link_pk);

ALTER TABLE lti2_resource_link
  ADD INDEX lti2_resource_link_consumer_pk_IDX (consumer_pk ASC);

ALTER TABLE lti2_resource_link
  ADD INDEX lti2_resource_link_context_pk_IDX (context_pk ASC);

CREATE TABLE lti2_user_result (
  user_pk              INT(11) AUTO_INCREMENT,
  resource_link_pk     INT(11)       NOT NULL,
  lti_user_id          VARCHAR(255)  NOT NULL,
  lti_result_sourcedid VARCHAR(1024) NOT NULL,
  created              DATETIME      NOT NULL,
  updated              DATETIME      NOT NULL,
  PRIMARY KEY (user_pk)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = latin1;

ALTER TABLE lti2_user_result
  ADD CONSTRAINT lti2_user_result_lti2_resource_link_FK1 FOREIGN KEY (resource_link_pk)
REFERENCES lti2_resource_link (resource_link_pk);

ALTER TABLE lti2_user_result
  ADD INDEX lti2_user_result_resource_link_pk_IDX (resource_link_pk ASC);

CREATE TABLE lti2_share_key (
  share_key_id     VARCHAR(32) NOT NULL,
  resource_link_pk INT(11)     NOT NULL,
  auto_approve     TINYINT(1)  NOT NULL,
  expires          DATETIME    NOT NULL,
  PRIMARY KEY (share_key_id)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = latin1;

ALTER TABLE lti2_share_key
  ADD CONSTRAINT lti2_share_key_lti2_resource_link_FK1 FOREIGN KEY (resource_link_pk)
REFERENCES lti2_resource_link (resource_link_pk);

ALTER TABLE lti2_share_key
  ADD INDEX lti2_share_key_resource_link_pk_IDX (resource_link_pk ASC);

        ');
        } elseif ($dbPlatform === 'postgresql') {
            //Table: lti2_consumer
            $this->addSql('DROP SEQUENCE IF EXISTS lti2_consumer_consumer_pk_seq CASCADE;');
            $this->addSql('CREATE SEQUENCE lti2_consumer_consumer_pk_seq INCREMENT BY 1 NO MAXVALUE NO MINVALUE CACHE 1;');
            $this->addSql('SELECT pg_catalog.setval(\'lti2_consumer_consumer_pk_seq\', 2, true);');

            $this->addSql('DROP TABLE IF EXISTS "lti2_consumer" CASCADE;');
            $this->addSql('CREATE TABLE "lti2_consumer" (
                "consumer_pk" integer DEFAULT nextval(\'lti2_consumer_consumer_pk_seq\'::regclass) NOT NULL,
                "name" character varying(50) NOT NULL,
                "consumer_key256" character varying(256) NOT NULL,
                "consumer_key" text,
                "secret" character varying(1024) NOT NULL,
                "lti_version" character varying(10),
                "consumer_name" character varying(255),
                "consumer_version" character varying(255),
                "consumer_guid" character varying(1024),
                "profile" text,
                "tool_proxy" text,
                "settings" text,
                "protected" boolean NOT NULL,
                "enabled" boolean NOT NULL,
                "enable_from" timestamp without time zone,
                "enable_until" timestamp without time zone,
                "last_access" date,
                "created" timestamp without time zone NOT NULL,
                "updated" timestamp without time zone NOT NULL
                )
                WITHOUT OIDS;');

            //table: lti2_context
            $this->addSql('DROP SEQUENCE IF EXISTS lti2_context_context_pk_seq CASCADE;');
            $this->addSql('CREATE SEQUENCE lti2_context_context_pk_seq INCREMENT BY 1 NO MAXVALUE NO MINVALUE CACHE 1;');
            $this->addSql('SELECT pg_catalog . setval(\'lti2_context_context_pk_seq\', 2, true);');

            $this->addSql('DROP TABLE IF EXISTS "lti2_context" CASCADE;');
            $this->addSql('CREATE TABLE "lti2_context" (
                "context_pk" integer DEFAULT nextval(\'lti2_context_context_pk_seq\'::regclass) NOT NULL,
                "consumer_pk" integer NOT NULL,
                "lti_context_id" character varying(255) NOT NULL,
                "settings" text,
                "created" timestamp without time zone NOT NULL,
                "updated" timestamp without time zone NOT NULL
                )
                WITHOUT OIDS;');


            //Table: lti2_nonce
            $this->addSql('DROP TABLE IF EXISTS "lti2_nonce" CASCADE;');
            $this->addSql('CREATE TABLE "lti2_nonce" (
                "consumer_pk" integer NOT NULL,
                "value" character varying(32) NOT NULL,
                "expires" timestamp without time zone NOT NULL
                )
                WITHOUT OIDS;');

            $this->addSql('DROP SEQUENCE IF EXISTS lti2_resource_link_resource_link_pk_seq CASCADE;');
            $this->addSql('CREATE SEQUENCE lti2_resource_link_resource_link_pk_seq INCREMENT BY 1 NO MAXVALUE NO MINVALUE CACHE 1;');
            $this->addSql('SELECT pg_catalog.setval(\'lti2_resource_link_resource_link_pk_seq\', 2, true);');

            //Table: lti2_resource_link
            $this->addSql('DROP TABLE IF EXISTS "lti2_resource_link" CASCADE;');
            $this->addSql('CREATE TABLE "lti2_resource_link" (
                "resource_link_pk" integer DEFAULT nextval(\'lti2_resource_link_resource_link_pk_seq\'::regclass) NOT NULL,
                "context_pk" integer,
                "consumer_pk" integer,
                "lti_resource_link_id" character varying(255) NOT NULL,
                "settings" text,
                "primary_resource_link_pk" integer,
                "share_approved" boolean,
                "created" timestamp without time zone NOT NULL,
                "updated" timestamp without time zone NOT NULL
                )
                WITHOUT OIDS;');

            //Table: lti2_share_key
            $this->addSql('DROP TABLE IF EXISTS "lti2_share_key" CASCADE;');
            $this->addSql('CREATE TABLE "lti2_share_key" (
                "share_key_id" character varying(32) NOT NULL,
                "resource_link_pk" integer NOT NULL,
                "auto_approve" boolean NOT NULL,
                "expires" timestamp without time zone NOT NULL
                )
                WITHOUT OIDS;');

            $this->addSql('DROP SEQUENCE IF EXISTS lti2_tool_proxy_tool_proxy_pk_seq CASCADE;');
            $this->addSql('CREATE SEQUENCE lti2_tool_proxy_tool_proxy_pk_seq INCREMENT BY 1 NO MAXVALUE NO MINVALUE CACHE 1;');
            $this->addSql('SELECT pg_catalog.setval(\'lti2_tool_proxy_tool_proxy_pk_seq\', 1, true);');

            //Table: lti2_tool_proxy
            $this->addSql('DROP TABLE IF EXISTS "lti2_tool_proxy" CASCADE;');
            $this->addSql('CREATE TABLE "lti2_tool_proxy" (
                "tool_proxy_pk" integer DEFAULT nextval(\'lti2_tool_proxy_tool_proxy_pk_seq\'::regclass) NOT NULL,
                "tool_proxy_id" character varying(32) NOT NULL,
                "consumer_pk" integer NOT NULL,
                "tool_proxy" text NOT NULL,
                "created" timestamp without time zone NOT NULL,
                "updated" timestamp without time zone NOT NULL
                )
                WITHOUT OIDS;');

            $this->addSql('DROP SEQUENCE IF EXISTS lti2_user_result_user_pk_seq CASCADE;');
            $this->addSql('CREATE SEQUENCE lti2_user_result_user_pk_seq INCREMENT BY 1 NO MAXVALUE NO MINVALUE CACHE 1;');
            $this->addSql('SELECT pg_catalog.setval(\'lti2_user_result_user_pk_seq\', 2, true);');

            //Table: lti2_user_result
            $this->addSql('DROP TABLE IF EXISTS "lti2_user_result" CASCADE;');
            $this->addSql('CREATE TABLE "lti2_user_result" (
                "user_pk" integer DEFAULT nextval(\'lti2_user_result_user_pk_seq\'::regclass) NOT NULL,
                "resource_link_pk" integer NOT NULL,
                "lti_user_id" character varying(255) NOT NULL,
                "lti_result_sourcedid" character varying(1024) NOT NULL,
                "created" timestamp without time zone NOT NULL,
                "updated" timestamp without time zone NOT NULL
                )
                WITHOUT OIDS;');

            $this->addSql('ALTER TABLE "lti2_consumer" ADD CONSTRAINT "lti2_consumer_consumer_pk_pkey" PRIMARY KEY(consumer_pk);');
            $this->addSql('DROP INDEX IF EXISTS "lti2_consumer_consumer_key256" CASCADE;');
            $this->addSql('CREATE UNIQUE INDEX "lti2_consumer_consumer_key256" ON "lti2_consumer" ("consumer_key256");');
            $this->addSql('ALTER TABLE "lti2_context" ADD CONSTRAINT "lti2_context_context_pk_pkey" PRIMARY KEY(context_pk);');
            $this->addSql('DROP INDEX IF EXISTS "lti2_context_consumer_pk" CASCADE;');
            $this->addSql('CREATE INDEX "lti2_context_consumer_pk" ON "lti2_context" ("consumer_pk");');
            $this->addSql('ALTER TABLE "lti2_nonce" ADD CONSTRAINT "lti2_nonce_consumer_pk_value_pkey" PRIMARY KEY(consumer_pk, value);');
            $this->addSql('ALTER TABLE "lti2_resource_link" ADD CONSTRAINT "lti2_resource_link_resource_link_pk_pkey" PRIMARY KEY(resource_link_pk);');
            $this->addSql('DROP INDEX IF EXISTS "lti2_resource_link_primary_resource_link_pk" CASCADE;');
            $this->addSql('CREATE INDEX "lti2_resource_link_primary_resource_link_pk" ON "lti2_resource_link" ("primary_resource_link_pk");');
            $this->addSql('DROP INDEX IF EXISTS "lti2_resource_link_consumer_pk" CASCADE;');
            $this->addSql('CREATE INDEX "lti2_resource_link_consumer_pk" ON "lti2_resource_link" ("consumer_pk");');
            $this->addSql('DROP INDEX IF EXISTS "lti2_resource_link_context_pk" CASCADE;');
            $this->addSql('CREATE INDEX "lti2_resource_link_context_pk" ON "lti2_resource_link" ("context_pk");');
            $this->addSql('ALTER TABLE "lti2_share_key" ADD CONSTRAINT "lti2_share_key_share_key_id_pkey" PRIMARY KEY(share_key_id);');
            $this->addSql('DROP INDEX IF EXISTS "lti2_share_key_resource_link_pk" CASCADE;');
            $this->addSql('CREATE INDEX "lti2_share_key_resource_link_pk" ON "lti2_share_key" ("resource_link_pk");');
            $this->addSql('ALTER TABLE "lti2_tool_proxy" ADD CONSTRAINT "lti2_tool_proxy_tool_proxy_pk_pkey" PRIMARY KEY(tool_proxy_pk);');
            $this->addSql('DROP INDEX IF EXISTS "lti2_tool_proxy_tool_proxy_id" CASCADE;');
            $this->addSql('CREATE UNIQUE INDEX "lti2_tool_proxy_tool_proxy_id" ON "lti2_tool_proxy" ("tool_proxy_id");');
            $this->addSql('DROP INDEX IF EXISTS "lti2_tool_proxy_consumer_pk" CASCADE;');
            $this->addSql('CREATE INDEX "lti2_tool_proxy_consumer_pk" ON "lti2_tool_proxy" ("consumer_pk");');
            $this->addSql('ALTER TABLE "lti2_user_result" ADD CONSTRAINT "lti2_user_result_user_pk_pkey" PRIMARY KEY(user_pk);');
            $this->addSql('DROP INDEX IF EXISTS "lti2_user_result_resource_link_pk" CASCADE;');
            $this->addSql('CREATE INDEX "lti2_user_result_resource_link_pk" ON "lti2_user_result" ("resource_link_pk");');
            $this->addSql('ALTER TABLE "lti2_context" ADD FOREIGN KEY ("consumer_pk") REFERENCES "lti2_consumer"(consumer_pk);');
            $this->addSql('ALTER TABLE "lti2_nonce" ADD FOREIGN KEY ("consumer_pk") REFERENCES "lti2_consumer"(consumer_pk);');
            $this->addSql('ALTER TABLE "lti2_resource_link" ADD FOREIGN KEY ("context_pk") REFERENCES "lti2_context"(context_pk);');
            $this->addSql('ALTER TABLE "lti2_resource_link" ADD FOREIGN KEY ("primary_resource_link_pk") REFERENCES "lti2_resource_link"(resource_link_pk);');
            $this->addSql('ALTER TABLE "lti2_share_key" ADD FOREIGN KEY ("resource_link_pk") REFERENCES "lti2_resource_link"(resource_link_pk);');
            $this->addSql('ALTER TABLE "lti2_tool_proxy" ADD FOREIGN KEY ("consumer_pk") REFERENCES "lti2_consumer"(consumer_pk);');
            $this->addSql('ALTER TABLE "lti2_user_result" ADD FOREIGN KEY ("resource_link_pk") REFERENCES "lti2_resource_link"(resource_link_pk);');
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}