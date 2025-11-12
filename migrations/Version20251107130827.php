<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251107130827 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "order" DROP CONSTRAINT fk_f52993981ad5cdbf');
        $this->addSql('DROP SEQUENCE cart_id_seq CASCADE');
        $this->addSql('ALTER TABLE cart DROP CONSTRAINT fk_ba388b77e3c61f9');
        $this->addSql('ALTER TABLE cart_plant DROP CONSTRAINT fk_f4e9607a1ad5cdbf');
        $this->addSql('ALTER TABLE cart_plant DROP CONSTRAINT fk_f4e9607a1d935652');
        $this->addSql('DROP TABLE cart');
        $this->addSql('DROP TABLE cart_plant');
        $this->addSql('DROP INDEX uniq_f52993981ad5cdbf');
        $this->addSql('ALTER TABLE "order" DROP cart_id');
        $this->addSql('ALTER TABLE order_details DROP CONSTRAINT fk_845ca2c18d9f6d38');
        $this->addSql('DROP INDEX uniq_845ca2c18d9f6d38');
        $this->addSql('ALTER TABLE order_details RENAME COLUMN order_id TO the_order_id');
        $this->addSql('ALTER TABLE order_details ADD CONSTRAINT FK_845CA2C1C416F85B FOREIGN KEY (the_order_id) REFERENCES "order" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_845CA2C1C416F85B ON order_details (the_order_id)');
        $this->addSql('ALTER TABLE plant ADD image_url VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ALTER first_name TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE "user" ALTER last_name TYPE VARCHAR(255)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE cart_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE cart (id SERIAL NOT NULL, owner_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_ba388b77e3c61f9 ON cart (owner_id)');
        $this->addSql('CREATE TABLE cart_plant (cart_id INT NOT NULL, plant_id INT NOT NULL, PRIMARY KEY(cart_id, plant_id))');
        $this->addSql('CREATE INDEX idx_f4e9607a1ad5cdbf ON cart_plant (cart_id)');
        $this->addSql('CREATE INDEX idx_f4e9607a1d935652 ON cart_plant (plant_id)');
        $this->addSql('ALTER TABLE cart ADD CONSTRAINT fk_ba388b77e3c61f9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE cart_plant ADD CONSTRAINT fk_f4e9607a1ad5cdbf FOREIGN KEY (cart_id) REFERENCES cart (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE cart_plant ADD CONSTRAINT fk_f4e9607a1d935652 FOREIGN KEY (plant_id) REFERENCES plant (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" ALTER first_name TYPE VARCHAR(50)');
        $this->addSql('ALTER TABLE "user" ALTER last_name TYPE VARCHAR(50)');
        $this->addSql('ALTER TABLE order_details DROP CONSTRAINT FK_845CA2C1C416F85B');
        $this->addSql('DROP INDEX UNIQ_845CA2C1C416F85B');
        $this->addSql('ALTER TABLE order_details RENAME COLUMN the_order_id TO order_id');
        $this->addSql('ALTER TABLE order_details ADD CONSTRAINT fk_845ca2c18d9f6d38 FOREIGN KEY (order_id) REFERENCES "order" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_845ca2c18d9f6d38 ON order_details (order_id)');
        $this->addSql('ALTER TABLE plant DROP image_url');
        $this->addSql('ALTER TABLE "order" ADD cart_id INT NOT NULL');
        $this->addSql('ALTER TABLE "order" ADD CONSTRAINT fk_f52993981ad5cdbf FOREIGN KEY (cart_id) REFERENCES cart (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_f52993981ad5cdbf ON "order" (cart_id)');
    }
}
