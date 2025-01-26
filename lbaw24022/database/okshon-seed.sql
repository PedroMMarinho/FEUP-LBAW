DROP SCHEMA IF EXISTS okshon CASCADE;
CREATE SCHEMA IF NOT EXISTS okshon;
SET search_path = 'okshon';

DROP INDEX IF EXISTS idx_follow_user_followed;
DROP INDEX IF EXISTS idx_auction_state;
DROP INDEX IF EXISTS idx_auction_category;
DROP INDEX IF EXISTS idx_follow_auction_auction;
DROP INDEX IF EXISTS idx_bid_auction;
DROP INDEX IF EXISTS idx_auto_bid_auction;
DROP INDEX IF EXISTS idx_message_auction;
DROP INDEX IF EXISTS idx_image_auction;
DROP INDEX IF EXISTS idx_notification_user;

DROP TABLE IF EXISTS auction CASCADE;
DROP TABLE IF EXISTS notification CASCADE;
DROP TABLE IF EXISTS message CASCADE;
DROP TABLE IF EXISTS image CASCADE;
DROP TABLE IF EXISTS category CASCADE;
DROP TABLE IF EXISTS auto_bid CASCADE;
DROP TABLE IF EXISTS bid CASCADE;
DROP TABLE IF EXISTS follow_auction CASCADE;
DROP TABLE IF EXISTS report_auction CASCADE;
DROP TABLE IF EXISTS report_user CASCADE;
DROP TABLE IF EXISTS block CASCADE;
DROP TABLE IF EXISTS rate_user CASCADE;
DROP TABLE IF EXISTS follow_user CASCADE;
DROP TABLE IF EXISTS subscription CASCADE;
DROP TABLE IF EXISTS expert_category CASCADE;
DROP TABLE IF EXISTS admin_change CASCADE;
DROP TABLE IF EXISTS expert CASCADE;
DROP TABLE IF EXISTS admin CASCADE;
DROP TABLE IF EXISTS users CASCADE;
DROP TABLE IF EXISTS general_user CASCADE;
DROP TABLE IF EXISTS user_transaction CASCADE;
DROP TABLE IF EXISTS site_config CASCADE;
DROP TABLE IF EXISTS advertisement CASCADE;

DROP TYPE IF EXISTS auction_state_enum CASCADE;
DROP TYPE IF EXISTS notification_type_enum CASCADE;
DROP TYPE IF EXISTS transaction_type_enum CASCADE;
DROP TYPE IF EXISTS attribute_type_enum CASCADE;
DROP TYPE IF EXISTS user_role_enum CASCADE;

CREATE TYPE transaction_type_enum AS ENUM ('Auction', 'Advertisement', 'Subscription', 'Wallet');
CREATE TYPE attribute_type_enum AS ENUM ('Default', 'Enum', 'Float', 'Int');
CREATE TYPE notification_type_enum AS ENUM ('New Bid', 'New Message', 'New Auction', 'Evaluation', 'Auction Closed', 'Auction Canceled', 'Auction Ending', 'Auction Reported', 'User Reported', 'Rating', 'Shipping', 'Blocked', 'Unblocked', 'Subscription End', 'Advertisement End', 'Auto Bid', 'User Follow', 'User Unfollow', 'Auction Follow', 'Auction Unfollow');
CREATE TYPE auction_state_enum AS ENUM ('Draft', 'Active', 'Canceled', 'Finished', 'Shipped', 'Finished without bids');
CREATE TYPE user_role_enum AS ENUM ('Regular User', 'Admin', 'Expert');


CREATE TABLE general_user (
    id SERIAL PRIMARY KEY,
    username VARCHAR NOT NULL UNIQUE,
    role user_role_enum NOT NULL,
    email VARCHAR NOT NULL UNIQUE,
    password VARCHAR,
    description TEXT,
    email_verified_at TIMESTAMP DEFAULT NULL,
    remember_token VARCHAR,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    google_id VARCHAR
);

CREATE TABLE users (
    id INTEGER PRIMARY KEY REFERENCES general_user(id) ON DELETE CASCADE,
    wallet NUMERIC DEFAULT 0,
    available_balance NUMERIC DEFAULT 0 CHECK (available_balance <= wallet),
    blocked BOOLEAN NOT NULL DEFAULT FALSE,
    subscribed BOOLEAN NOT NULL DEFAULT FALSE,


    following_auction_new_bid_notifications BOOLEAN NOT NULL DEFAULT TRUE, 
    selling_auction_new_bid_notifications BOOLEAN NOT NULL DEFAULT TRUE,   
    new_message_notifications BOOLEAN NOT NULL DEFAULT TRUE,               
    new_auction_notifications BOOLEAN NOT NULL DEFAULT TRUE,               
    following_auction_closed_notifications BOOLEAN NOT NULL DEFAULT TRUE,   
    following_auction_canceled_notifications BOOLEAN NOT NULL DEFAULT TRUE, 
    following_auction_ending_notifications BOOLEAN NOT NULL DEFAULT TRUE,
    seller_auction_ending_notifications BOOLEAN NOT NULL DEFAULT TRUE,    
    bidder_auction_ending_notifications BOOLEAN NOT NULL DEFAULT TRUE,
    rating_notifications BOOLEAN NOT NULL DEFAULT TRUE                  
);


CREATE TABLE expert (
    id INTEGER PRIMARY KEY REFERENCES general_user(id) ON DELETE CASCADE
);


CREATE TABLE admin (
    id INTEGER PRIMARY KEY REFERENCES general_user(id) ON DELETE CASCADE
);


CREATE TABLE admin_change (
    id SERIAL PRIMARY KEY,
    description TEXT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    admin INTEGER REFERENCES admin(id) ON DELETE SET DEFAULT DEFAULT 2
);


CREATE TABLE report_user (
    id SERIAL PRIMARY KEY,
    description TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reported INTEGER REFERENCES users(id) ON DELETE CASCADE,
    reporter INTEGER REFERENCES users(id) ON DELETE SET DEFAULT DEFAULT 1
);

CREATE TABLE block (
    id SERIAL PRIMARY KEY,
    start_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    end_time TIMESTAMP DEFAULT '2999-12-31 23:59:59' CHECK (end_time > start_time) NOT NULL,
    block_message TEXT NOT NULL,
    appeal_message TEXT DEFAULT NULL,
    appeal_accepted BOOLEAN DEFAULT NULL,
    report INTEGER REFERENCES report_user(id) ON DELETE SET NULL,
    block_admin INTEGER REFERENCES admin(id) ON DELETE SET DEFAULT DEFAULT 2,
    blocked_user INTEGER REFERENCES users(id) ON DELETE CASCADE,
    appeal_admin INTEGER REFERENCES admin(id)
);


CREATE TABLE rate_user (
    id SERIAL PRIMARY KEY,
    rate INTEGER CHECK (rate >= 1 AND rate <= 5),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    rated_user INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    rater_user INTEGER NOT NULL REFERENCES users(id) ON DELETE SET DEFAULT DEFAULT 1
);


CREATE TABLE follow_user (
    id SERIAL PRIMARY KEY,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    followed_user INTEGER REFERENCES users(id) ON DELETE CASCADE,
    follower_user INTEGER REFERENCES users(id) ON DELETE CASCADE
);


CREATE TABLE subscription (
    id SERIAL PRIMARY KEY,
    start_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    end_time TIMESTAMP NOT NULL CHECK (end_time > start_time),
    cost NUMERIC NOT NULL CHECK (cost > 0),
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE
);


CREATE TABLE category (
    id SERIAL PRIMARY KEY,
    name VARCHAR NOT NULL UNIQUE,
    attribute_list JSON DEFAULT NULL
);


CREATE TABLE expert_category (
    id SERIAL PRIMARY KEY,
    expert INTEGER REFERENCES expert(id) ON DELETE CASCADE,
    category_id INTEGER REFERENCES category(id) -- ON DELETE SET DEFAULT 0
);


CREATE TABLE auction (
    id SERIAL PRIMARY KEY,
    name VARCHAR NOT NULL,
    description TEXT NOT NULL,
    location VARCHAR NOT NULL,
    creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    end_time TIMESTAMP NOT NULL CHECK (end_time > creation + INTERVAL '2 hours' AND end_time < creation + INTERVAL '31 days'),
    minimum_bid NUMERIC NOT NULL DEFAULT 1 CHECK (minimum_bid > 0),    
    delivery_location TEXT DEFAULT NULL,
    advertised BOOLEAN NOT NULL DEFAULT FALSE,
    evaluation_requested BOOLEAN NOT NULL DEFAULT FALSE,
    visitors INTEGER DEFAULT 0 CHECK (visitors >= 0),
    auction_state auction_state_enum DEFAULT 'Draft',
    seller_id INTEGER NOT NULL REFERENCES users(id),
    category_id INTEGER NOT NULL REFERENCES category(id), -- ON DELETE SET DEFAULT DEFAULT 0
    attribute_values JSON NOT NULL,
    evaluation NUMERIC DEFAULT NULL CHECK (evaluation IS NULL OR evaluation > 0),
    expert INTEGER REFERENCES expert(id)
);


CREATE TABLE report_auction (
    id SERIAL PRIMARY KEY,
    description TEXT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reporter INTEGER REFERENCES users(id) ON DELETE SET DEFAULT DEFAULT 1,
    auction INTEGER REFERENCES auction(id) ON DELETE CASCADE 
);


CREATE TABLE follow_auction (
    id SERIAL PRIMARY KEY,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    follower INTEGER REFERENCES users(id) ON DELETE CASCADE,
    auction INTEGER REFERENCES auction(id) ON DELETE CASCADE
);


CREATE TABLE auto_bid (
    id SERIAL PRIMARY KEY,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    active BOOLEAN DEFAULT TRUE,
    max NUMERIC CHECK (max > 0),
    bidder INTEGER REFERENCES users(id) ON DELETE CASCADE,
    auction INTEGER REFERENCES auction(id) ON DELETE CASCADE
);


CREATE TABLE bid (
    id SERIAL PRIMARY KEY,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    value NUMERIC CHECK (value > 0),
    bidder INTEGER REFERENCES users(id),
    auction INTEGER REFERENCES auction(id)
);


CREATE TABLE advertisement (
    id SERIAL PRIMARY KEY,
    start_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    end_time TIMESTAMP NOT NULL CHECK (end_time > start_time),
    cost NUMERIC NOT NULL CHECK (cost > 0),
    auction_id INTEGER REFERENCES auction(id) ON DELETE CASCADE
);


CREATE TABLE message (
    id SERIAL PRIMARY KEY,
    content TEXT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    auction INTEGER REFERENCES auction(id) ON DELETE CASCADE,
    general_user_id INTEGER REFERENCES general_user(id) ON DELETE SET DEFAULT DEFAULT 1
);


CREATE TABLE notification (
    id SERIAL PRIMARY KEY,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    viewed BOOLEAN NOT NULL DEFAULT FALSE,
    description TEXT NOT NULL,
    general_user_id INTEGER REFERENCES general_user(id) ON DELETE CASCADE,
    notification_type notification_type_enum,
    follower_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    auction INTEGER REFERENCES auction(id) ON DELETE CASCADE,
    bid INTEGER REFERENCES bid(id) ON DELETE CASCADE,
    report INTEGER REFERENCES report_user(id) ON DELETE CASCADE,
    block INTEGER REFERENCES block(id) ON DELETE CASCADE,
    rate_user INTEGER REFERENCES rate_user(id) ON DELETE CASCADE,
    report_auction INTEGER REFERENCES report_auction(id) ON DELETE CASCADE
);

CREATE TABLE user_transaction (
    id SERIAL PRIMARY KEY,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    amount NUMERIC NOT NULL,
    transaction_type transaction_type_enum NOT NULL,
    winner_bid INTEGER REFERENCES bid(id) ON DELETE SET NULL, -- lidar quando mostrar 
    seller_id INTEGER REFERENCES users(id) ON DELETE SET DEFAULT DEFAULT 1,
    user_id INTEGER REFERENCES users(id) ON DELETE SET DEFAULT DEFAULT 1,
    advertisement_id INTEGER REFERENCES advertisement(id) ON DELETE SET NULL, -- lidar quando mostrar 
    subscription_id INTEGER REFERENCES subscription(id) ON DELETE SET NULL -- lidar quando mostrar 
);

CREATE TABLE site_config(
    id SERIAL PRIMARY KEY,
    subscribe_price NUMERIC NOT NULL DEFAULT 10,
    subscribe_price_plan_a NUMERIC NOT NULL,
    subscribe_price_plan_b NUMERIC NOT NULL,
    subscribe_price_plan_c NUMERIC NOT NULL,
    ad_price NUMERIC NOT NULL DEFAULT 2,
    discounted_ad_price NUMERIC NOT NULL DEFAULT 2,
    minimal_bid_interval NUMERIC NOT NULL DEFAULT 1
);

CREATE TABLE image (
    id SERIAL PRIMARY KEY,
    path VARCHAR,
    general_user_id INTEGER REFERENCES general_user(id) ON DELETE CASCADE,
    auction INTEGER REFERENCES auction(id) ON DELETE CASCADE
);




CREATE INDEX idx_follow_user_followed ON follow_user USING HASH (followed_user);

CREATE INDEX idx_auction_state ON auction USING BTREE (auction_state);

CREATE INDEX idx_auction_category ON auction USING HASH (category_id);

CREATE INDEX idx_follow_auction_auction ON follow_auction USING HASH (auction);

CREATE INDEX idx_bid_auction ON bid USING HASH (auction);

CREATE INDEX idx_auto_bid_auction ON auto_bid USING HASH (auction);


CREATE INDEX idx_message_auction ON message USING HASH (auction);

CREATE INDEX idx_image_auction ON image USING HASH (auction);

CREATE INDEX idx_notification_user ON notification USING HASH (general_user_id);


--populate
INSERT INTO general_user (username, role, email, password, description, email_verified_at) VALUES 
    ('USER', 'Regular User', 'userdefault@okshon.com', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', 'Lets start this bidding fest :)', CURRENT_TIMESTAMP),
    ('ADMIN', 'Admin', 'admindefault@okshon.com', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W' , 'Mantaining the site safe and sound!', CURRENT_TIMESTAMP),
    ('EXPERT', 'Expert', 'expertdefault@okshon.com', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W' ,'Best expert around be sure to request my services!', CURRENT_TIMESTAMP);


INSERT INTO general_user (username, role, email, password, email_verified_at) VALUES 
    ('Hugo', 'Regular User', 'user1@example.com', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', CURRENT_TIMESTAMP),
    ('José', 'Regular User', 'user2@example.com', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', CURRENT_TIMESTAMP),
    ('Firmina', 'Expert', 'expert1@example.com', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', CURRENT_TIMESTAMP),
    ('Peligrin', 'Admin', 'admin1@example.com', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', CURRENT_TIMESTAMP),
    ('TheBest', 'Regular User', 'thebest@example.com', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', CURRENT_TIMESTAMP),
    ('User3', 'Regular User', 'user3@example.com', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', CURRENT_TIMESTAMP),
    ('Paula', 'Expert', 'expert2@example.com', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', CURRENT_TIMESTAMP),
    ( 'Supervisor', 'Admin', 'admin2@example.com', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', CURRENT_TIMESTAMP),
    ( 'Tocas', 'Regular User', 'guest1@example.com', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', CURRENT_TIMESTAMP),
    ( 'Augusta', 'Regular User', 'guest2@example.com', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', CURRENT_TIMESTAMP),
    ( 'Maria', 'Admin', 'moderator1@example.com', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', CURRENT_TIMESTAMP),
    ( 'Anita', 'Regular User', 'user4@example.com', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', CURRENT_TIMESTAMP),
    ( 'João', 'Expert', 'expert3@example.com', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', CURRENT_TIMESTAMP),
    ( 'Constantino', 'Admin', 'admin3@example.com', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', CURRENT_TIMESTAMP),
    ( 'Patricia', 'Regular User', 'user5@example.com', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', CURRENT_TIMESTAMP),
    ( 'James', 'Regular User', 'jessie@example.com', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', CURRENT_TIMESTAMP),
    ( 'Filomeno', 'Expert', 'expert4@example.com', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', CURRENT_TIMESTAMP);
INSERT INTO general_user (username, role, email, password, email_verified_at,description) VALUES 
    ( 'Walter', 'Regular User', 'white@example.com', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', CURRENT_TIMESTAMP, 'Lets cook some bids!');
INSERT INTO general_user (username, role, email, password, email_verified_at) VALUES     
    ( 'Jesus', 'Admin', 'moderator2@example.com', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', CURRENT_TIMESTAMP),
    ( 'Tino', 'Regular User', 'dehero@example.com', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', CURRENT_TIMESTAMP),
    ( 'Jessie', 'Regular User', 'richard@example.com', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', CURRENT_TIMESTAMP),
    ( 'Gus', 'Regular User', 'berry@example.com', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', CURRENT_TIMESTAMP);

-- Password is 1234. Generated using Hash::make('1234')


INSERT INTO users (id, wallet, available_balance, blocked, subscribed) VALUES
    (1, 0, 0, FALSE, FALSE),
    (4, 100, 100, FALSE, TRUE),
    (5, 2500, 660, FALSE, TRUE),
    (8, 16000, 300, FALSE, FALSE),
    (9, 0, 0, FALSE, FALSE),
    (12, 200, 200, FALSE, TRUE),
    (13, 1200, 1200, FALSE, TRUE),
    (15, 200, 0, FALSE, FALSE),
    (18, 220, 220, TRUE, FALSE),
    (19, 600, 600, TRUE, FALSE),
    (21, 7000, 7000, FALSE, FALSE),
    (23, 150, 150, TRUE, FALSE),
    (24, 40000, 40000, FALSE, TRUE),
    (25, 50000, 34800, FALSE, FALSE);


INSERT INTO admin (id) VALUES
    (2),
    (7), 
    (11), 
    (14), 
    (17),
    (22); 


INSERT INTO expert (id) VALUES
    (3),
    (6),
    (10),
    (16),
    (20);

INSERT INTO admin_change (description, admin)
VALUES 
    ('Updated auction details', 7),
    ('Deleted user account', 11),
    ('Resolved dispute in auction', 17);


INSERT INTO category (name) VALUES ('Other');

INSERT INTO category (name, attribute_list) VALUES 
    ('Electronics', '[
        {
            "name": "color",
            "type": "enum",
            "required": false,
            "options": ["red", "blue", "green", "black", "white","grey", "silver", "gold"]
        },
        {
            "name": "condition",
            "type": "enum",
            "required": true,
            "options": ["old", "very old", "new", "brand new"]
        },
        {
            "name": "weight",
            "type": "float",
            "required": true
        },
        {
            "name": "brand",
            "type": "enum",
            "options": ["apple", "samsung", "xiaomi", "huawei", "sony","kodak"]
        },
        {
            "name": "type",
            "type": "enum",
            "required": true,
            "options": ["smartphone", "laptop", "tablet", "smartwatch", "drone", "camera", "television"]
        }

    ]'), 

    ('Furniture', '[
        {
            "name": "weight",
            "type": "float",
            "required": false
        },
        {
            "name": "material",
            "type": "enum",
            "required": true,
            "options": ["wood", "metal", "plastic", "glass", "fabric"]
        },
        {
            "name": "color",
            "type": "enum",
            "required": false,
            "options": ["red", "blue", "green", "black", "white", "brown", "grey", "beige"]
        }
    ]'), 

    ('Clothing', '[
        {
            "name": "size",
            "type": "enum",
            "required": true,
            "options": ["XS", "S", "M", "L", "XL", "XXL"]
        },
        {
            "name": "material",
            "type": "enum",
            "required": false,
            "options": ["cotton", "polyester", "wool", "silk", "leather"]
        }
    ]'),

    ('Books', '[
        {
            "name": "publication_year",
            "type": "int",
            "required": false
        },
        {
            "name": "type",
            "type": "enum",
            "required": true,
            "options": ["manga", "comic", "novel", "poetry", "cookbook", "encyclopedia", "textbook"]
        },
        {
            "name": "genre",
            "type": "enum",
            "required": true,
            "options": ["fiction", "non-fiction", "biography", "mystery", "thriller", "romance", "science fiction", "fantasy"]
        }
    ]'),('Vehicles', '[
        {
            "name": "make",
            "type": "enum",
            "required": true,
            "options": ["audi", "bmw", "mercedes", "volkswagen", "fiat", "renault", "peugeot", "toyota", "honda", "nissan", "ford", "chevrolet", "tesla"]
        },
        {
            "name": "model",
            "type": "enum",
            "required": true,
            "options": ["a3", "a4", "a6", "3 series", "5 series", "7 series", "c class", "e class", "s class", "golf", "polo", "passat", "punto", "500", "clio", "208", "308", "corolla", "civic", "micra", "focus", "fiesta", "mustang", "camaro", "model s", "model 3", "model x", "model y"]
        },
        {
            "name": "year",
            "type": "int",
            "required": true
        },
        {
            "name": "mileage",
            "type": "float",
            "required": false
        }
    ]'),('Jewelry', '[
        {
            "name": "weight",
            "type": "float",
            "required": false
        },
        {
            "name": "gemstone",
            "type": "enum",
            "required": false,
            "options": ["diamond", "ruby", "sapphire", "emerald", "topaz", "amethyst", "aquamarine", "garnet", "peridot", "opal"]
        },
        {
            "name": "material",
            "type": "enum",
            "required": true,
            "options": ["gold", "silver", "platinum"]
        }
    ]'),('Art', '[
        {
            "name": "medium",
            "type": "enum",
            "required": true,
            "options": ["painting", "sculpture", "digital", "print"]
        },
        {
            "name": "artist",
            "type": "enum",
            "required": false,
            "options": ["leonardo da vinci", "vincent van gogh", "pablo picasso", "salvador dali", "rembrandt", "michelangelo", "claude monet", "edvard munch", "andy warhol", "frida kahlo", "jackson pollock", "yayoi kusama", "banksy"]
        },
        {
            "name": "Production year",
            "type": "int",
            "required": false
        }
    ]'),('Beauty & Personal Care', '[
        {
            "name": "type",
            "type": "enum",
            "required": true,
            "options": ["makeup", "skincare", "haircare", "perfume"]
        },
        {
            "name": "brand",
            "type": "enum",
            "required": false,
            "options": ["loreal", "maybelline", "revlon", "nivea", "dove", "garnier", "clinique", "estee lauder", "mac", "chanel", "dior", "gucci", "versace"]
        },
        {
            "name": "size",
            "type": "float",
            "required": false
        }
    ]'), ('Garden & Outdoors', '[
        {
            "name": "type",
            "type": "enum",
            "required": true,
            "options": ["tools", "plants", "decor", "furniture"]
        },
        {
            "name": "material",
            "type": "enum",
            "required": true,
            "options": ["wood", "metal", "plastic", "glass", "fabric"]
        }
    ]'), ('Musical Instruments', '[
        {
            "name": "type",
            "type": "enum",
            "required": true,
            "options": ["string", "percussion", "wind", "electronic"]
        },
        {
            "name": "brand",
            "type": "enum",
            "required": true,
            "options": ["yamaha", "fender", "gibson", "roland", "korg", "pearl", "tama", "zildjian", "sabian", "dw", "ludwig", "gretsch", "taylor", "martin", "gibson", "epiphone", "ibanez", "prs", "jackson", "esp", "schecter"]
        },
        {
            "name": "year",
            "type": "int",
            "required": false
        },
        {
            "name": "condition",
            "type": "enum",
            "required": true,
            "options": ["new", "used", "vintage"]
        }
    ]'), ('Home Appliances', '[
        {
            "name": "type",
            "type": "enum",
            "required": true,
            "options": ["kitchen", "cleaning", "laundry", "heating", "cooling"]
        },
        {
            "name": "brand",
            "type": "enum",
            "required": true,
            "options": ["whirlpool", "samsung", "lg", "ge", "bosch", "miele", "siemens", "electrolux", "dyson", "shark", "honeywell", "nest", "ecobee", "lennox", "carrier", "trane", "rheem", "goodman", "daikin", "panasonic", "hitachi", "mitsubishi", "fujitsu", "toshiba"]
        },
        {
            "name": "energy_rating",
            "type": "enum",
            "required": true,
            "options": ["A+++", "A++", "A+", "A", "B", "C", "D", "E", "F", "G"]
        }
    ]'),('Real Estate', '[
        {
            "name": "type",
            "type": "enum",
            "required": true,
            "options": ["house", "apartment", "land", "commercial"]
        },
        {
            "name": "area",
            "type": "float",
            "required": true
        },
        {
            "name": "bedrooms",
            "type": "int",
            "required": false
        },
        {
            "name": "bathrooms",
            "type": "int",
            "required": false
        }
    ]'),('Games & Entertainment', '[
        {
            "name": "platform",
            "type": "enum",
            "required": true,
            "options": ["playstation", "xbox", "nintendo", "pc", "mobile"]
        },
        {
            "name": "type",
            "type": "enum",
            "required": true,
            "options": ["board game", "video game", "puzzle", "toy"]
        },
        {
            "name": "multiplayer",
            "type": "enum",
            "required": false,
            "options": ["yes", "no"]
        }
    ]');

INSERT INTO expert_category (expert, category_id) VALUES 
    (6, 1), 
    (10, 2), 
    (16, 3),
    (20, 5);  


INSERT INTO auction (name, description, location, end_time, minimum_bid, advertised, seller_id, category_id, attribute_values, auction_state) VALUES
    ('Vintage Camera', 'A vintage camera from the 1960s.', 'Lisbon', CURRENT_TIMESTAMP + INTERVAL '10 days', 50, TRUE, 4, 2, '{"color":"grey", "condition":"old", "weight":2.5, "brand":"kodak", "type":"camera"}', 'Active'),
    ('Antique Table', 'An antique wooden table.', 'Porto', CURRENT_TIMESTAMP + INTERVAL '5 days', 100, TRUE, 5, 3, '{"weight":"20.5","material" : "wood" , "color": "brown" }', 'Active'),
    ('Rare Coin Collection', 'A rare collection of ancient coins.', 'Coimbra', CURRENT_TIMESTAMP + INTERVAL '15 days', 500, FALSE, 4, 1, '{}', 'Finished'),
    ('Smartphone', 'A barely used smartphone in mint condition.', 'Braga', CURRENT_TIMESTAMP + INTERVAL '7 days', 300, FALSE, 4, 2, '{"color":"black", "condition":"brand new","weight" :"3.12","brand":"apple","type" : "smartphone"}', 'Active'),
    ('Designer Chair', 'A stylish and modern chair.', 'Aveiro', CURRENT_TIMESTAMP + INTERVAL '12 days', 200, FALSE, 5, 3, '{"weight":"8.75","material" : "plastic"}', 'Draft'),
    ('Classic Painting', 'A classic 18th-century oil painting.', 'Faro', CURRENT_TIMESTAMP + INTERVAL '20 days', 1000, FALSE, 9, 8, '{"medium":"painting" ,"artist" : "leonardo da vinci", "Production year": "1789"}', 'Active'),
    ('Retro Television', 'A fully functional retro TV from the 1980s.', 'Lisbon', CURRENT_TIMESTAMP + INTERVAL '8 days', 150, TRUE, 5, 2, '{"color":"black", "condition":"very old","weight" :"15","type" : "television"}', 'Active'),
    ('Oak Dining Table', 'A handcrafted oak dining table.', 'Porto', CURRENT_TIMESTAMP + INTERVAL '14 days', 400, FALSE, 8, 3, '{"weight":"50","material" : "wood" , "color": "brown" }', 'Draft'),
    ('Vintage Comic Book', 'First edition superhero comic.', 'Braga', CURRENT_TIMESTAMP + INTERVAL '20 days', 250, TRUE, 4, 5, '{"publication_year":"1900","type" : "comic" , "genre" : "fiction"}', 'Active'),
    ('Apple Laptop', 'High-performance productive laptop with 32GB RAM.', 'Lisbon', CURRENT_TIMESTAMP + INTERVAL '10 days', 1000, FALSE, 5, 2, '{"color":"silver", "condition":"new", "weigth" : "20","brand" : "apple"}', 'Active'),
    ('Antique Lamp', 'A 19th-century decorative lamp.', 'Faro', CURRENT_TIMESTAMP + INTERVAL '6 days', 120, FALSE, 4, 3, '{"weight":"3.2", "material" : "metal" , "color" : "black"}', 'Active'),
    ('Terrain in the moutains', 'A solid terrain where you can build your new house!', 'Coimbra', CURRENT_TIMESTAMP + INTERVAL '12 days', 90, FALSE, 12, 13, '{"type":"land", "area" : "1000.5"}', 'Finished without bids');

INSERT INTO auction (name, description, location, end_time, minimum_bid, delivery_location, advertised, seller_id, category_id, attribute_values, auction_state) VALUES
    ('Smart Watch', 'Latest model smartwatch with fitness tracking.', 'Lisbon', CURRENT_TIMESTAMP + INTERVAL '5 days', 250, 'Porto', FALSE, 5, 2, '{"color":"green", "condition":"brand new"}', 'Shipped');

INSERT INTO auction (name, description, location, end_time, minimum_bid, advertised, seller_id, category_id, attribute_values, auction_state) VALUES
    ('Luxury Sofa', 'Modern luxury leather sofa.', 'Porto', CURRENT_TIMESTAMP + INTERVAL '18 days', 800, TRUE, 21, 3, '{"weight":"100.5" , "material" : "fabric" ,"color" : "beige"}', 'Active'),
    ('Gold Ornaments', 'Pure gold ornaments with a unique design.', 'Faro', CURRENT_TIMESTAMP + INTERVAL '25 days', 9000, FALSE, 4, 7, '{"weight":"40.5", "material" : "gold"}', 'Active'),
    ('Drone', 'Quadcopter with HD camera and long battery life.', 'Aveiro', CURRENT_TIMESTAMP + INTERVAL '15 days', 350, FALSE, 5, 2, '{"color":"white", "condition":"new", "type" : "drone"}', 'Canceled'),
    ('Antique Mirrors', 'Beautifully carved antique mirrors.', 'Braga', CURRENT_TIMESTAMP + INTERVAL '7 days', 600, FALSE, 8, 3, '{"weight":"12.0", "material" : "glass", "color" : "brown"}', 'Active'),
    ('Diamond Ring', 'A beautifully carbonated diamond ring.', 'Beja', CURRENT_TIMESTAMP + INTERVAL '7 days', 8000, FALSE, 21, 7, '{"weight":"200.0", "gemstone" : "diamond"}', 'Active');

INSERT INTO auction (name, description, location, end_time, minimum_bid, delivery_location, advertised, seller_id, category_id, attribute_values, auction_state) VALUES
    ('Vintage Car', 'A rare vintage Car.', 'Coimbra', CURRENT_TIMESTAMP + INTERVAL '15 days', 7500, '', FALSE, 4, 6, '{"make":"toyota", "model":"punto", "year":"1965", "mileage":"10000"}', 'Active'),
    ('Vintage Car 2', 'A rare vintage Car 2.', 'Coimbra', CURRENT_TIMESTAMP + INTERVAL '15 days', 7500, 'Lisbon', FALSE, 4, 6, '{"make":"honda", "model":"polo", "year":"1975", "mileage":"20000"}', 'Shipped'),
    ('Vintage Car 3', 'A rare vintage Car 3.', 'Coimbra', CURRENT_TIMESTAMP + INTERVAL '15 days', 7500, 'Lisbon', FALSE, 4, 6, '{"make":"ford", "model":"model x", "year":"1955", "mileage":"30000"}', 'Finished'),
    ('Douro Wine Collection', 'Wine with refined aroma, resting for 30 years', 'Arouca', CURRENT_TIMESTAMP + INTERVAL '15 days', 7500, 'Lisbon', FALSE, 4, 1, '{}', 'Active'),
    ('Mercedes G-Wagon', 'Robust and fast car for everyday activities. Confortable with leader coating as well as great space inside. Especially good in difficult terrains!', 'Estremoz', CURRENT_TIMESTAMP + INTERVAL '15 days', 10000, 'Viseu', FALSE, 4, 6, '{"make":"mercedes", "year":"2024","mileage":"0","model" : "7 series"}', 'Active');

INSERT INTO image (path,auction) VALUES
    ('comic_book.jpg',3),
    ('vintage_car.jpg',3),

    ('antique_table.jpg',5),
    ('vintage_car.jpg',5),

    ('iphone.jpg',8),
    ('gold_necklace.jpg',8),

    ('sofa.jpg',12),
    ('wine_collection.jpg',12),

    ('classic_painting.jpg',13),
    ('antique_mirror.jpg',13),

    ('retro_tv.jpg',16),
    ('sofa.jpg',16),

    ('antique_lamp.jpg',20),
    ('sofa.jpg',20),

    ('mercedes_gwagen.jpg',21),
    ('vintage_camera.jpg',21),


    ('vintage_car.jpg',19),
    ('antique_table.jpg',19),
    ('classic_painting.jpg',19),
    ('iphone.jpg',19),
    ('antique_table.jpg',2),
    ('vintage_car.jpg',2),
    ('classic_painting.jpg',2),
    ('iphone.jpg',2),

    ('iphone.jpg',4),
    ('apple_laptop.jpg',4),
    ('retro_tv.jpg',4),
    ('vintage_car.jpg',4),

    ('classic_painting.jpg',6),
    ('gold_necklace.jpg',6),
    ('diamond_ring.jpg',6),
    ('wine_collection.jpg',6),

    ('vintage_camera.jpg', 1),
    ('comic_book.jpg', 1),
    
    ('comic_book.jpg', 9),
    ('apple_laptop.jpg', 9),
    ('antique_mirror.jpg', 9),
    ('diamond_ring.jpg', 9),

    ('retro_tv.jpg',7),
    ('sofa.jpg',7),

    ('apple_laptop.jpg',10),
    ('antique_table.jpg',10),
    ('iphone.jpg',10),
    ('mercedes_gwagen.jpg',10),
    ('vintage_car.jpg',10),
    ('retro_tv.jpg',10),

    ('antique_lamp.jpg',11),
    ('classic_painting.jpg',11),

    ('sofa.jpg',14),
    ('diamond_ring.jpg',14),

    ('gold_necklace.jpg',15),
    ('comic_book.jpg',15),

    ('antique_mirror.jpg',17),
    ('classic_painting.jpg',17),
    ('wine_collection.jpg',17),

    ('diamond_ring.jpg',18),
    ('gold_necklace.jpg',18),
    ('antique_mirror.jpg',18),
    ('iphone.jpg',18),

    ('wine_collection.jpg',22),
    ('antique_lamp.jpg',22),
    ('vintage_car.jpg',22),

    ('mercedes_gwagen.jpg',23),
    ('antique_lamp.jpg',23),
    ('antique_mirror.jpg',23);


INSERT INTO image (path,general_user_id) VALUES
    ('saul_goodman.jpg',1),
    ('admin_man.jpg',2),
    ('expert_woman.jpg',3),
    ('john.jpg',16),
    ('walter_white.jpg',21),
    ('old_man.jpg',23),
    ('jessie_pinkman.jpg',24),
    ('gustavo_fring.jpg',25),
    ('old_lady.jpg',6),
    ('woman.jpg',10);



INSERT INTO report_user (description, reported, reporter) VALUES
    ('User reported for spamming.', 21, 5),
    ('User reported for inappropriate behavior.', 5, 8),
    ('User reported for fraudulent activity.', 8, 4),
    ('User reported for harassment.', 4, 9),
    ('User reported for posting misleading information.', 5, 4),
    ('User reported for violating platform rules.', 23, 12),
    ('User reported for abusive language.', 19, 5),
    ('User reported for abusive language.', 19, 4),
    ('User reported for abusive language.', 19, 8),
    ('User reported for creating fake accounts.', 4, 8),
    ('User reported for sharing unauthorized content.', 5, 19),
    ('User reported for selling prohibited items.', 21, 4);

INSERT INTO report_auction (description, reporter, auction) VALUES
    ('Item description not accurate', 1, 3),
    ('Auction seems suspicious', 4, 7),
    ('Violation of rules', 5, 10),
    ('Misleading title', 8, 14),
    ('Inappropriate content', 9, 6),
    ('Duplicate auction', 12, 1),
    ('Auction seems abandoned', 13, 20),
    ('Violation of rules', 15, 5),
    ('Item not as described', 18, 19),
    ('Auction violates guidelines', 19, 11),
    ('Suspicious bidding activity', 4, 23),
    ('Suspicious bidding activity', 5, 23),
    ('Suspicious bidding activity', 8, 23),
    ('Suspicious bidding activity', 19, 23),
    ('Suspicious bidding activity', 18, 23),
    ('Offensive language in description', 4, 4),
    ('Offensive language in description', 5, 4),
    ('Offensive language in description', 24, 4),
    ('Offensive language in description', 25, 4),
    ('Price seems unusually high', 5, 2),
    ('Price seems unusually high', 24, 2),
    ('Price seems unusually high', 25, 2),
    ('Reported for review', 8, 18),
    ('Reported for review', 25, 18),
    ('Reported for review', 18, 18);



INSERT INTO block (block_message, end_time, block_admin, blocked_user) VALUES
    ('User blocked for suspicious activity.', '2025-12-27 18:25:00', 11, 18);

INSERT INTO block (block_message, appeal_message, block_admin, blocked_user) VALUES
    ('User blocked for multiple policy violations.', 'Foi errado! Não fiz nada de mal!!!', 7, 23),
    ('User blocked for multiple policy violations.', 'DESCULLPE!!!', 14, 19);


INSERT INTO rate_user (rate, rated_user, rater_user) VALUES
    (5, 4, 5),
    (4, 5, 8),
    (3, 9, 4),
    (2, 9, 8),
    (1, 8, 9),
    (5, 9, 12),
    (4, 12, 15),
    (3, 18, 12),
    (2, 12, 13),
    (1, 13, 12),
    (5, 8, 15),
    (4, 15, 13),
    (2, 23, 18),
    (1, 18, 19),
    (5, 19, 23);



-- Sample data for follow_user
INSERT INTO follow_user (followed_user, follower_user) VALUES
    (4, 5),
    (5, 4),
    (5, 8),
    (8, 5),
    (8, 4),
    (9, 8),
    (12, 8),
    (13, 8),
    (21, 9),
    (21, 12),
    (23, 4);

-- Sample data for follow_user
INSERT INTO follow_auction (follower, auction) VALUES
    (5, 1), 
    (8, 1),
    (12, 1), 
    (21, 2),
    (4, 2),
    (9, 3),
    (21, 4), 
    (23, 4),
    (12, 6), 
    (13, 6),
    (4, 6), 
    (4, 7),
    (8, 12),
    (23, 12),
    (21, 14), 
    (12, 15),
    (23, 16),
    (8, 16);

-- Sample data for subscription
INSERT INTO subscription (end_time, cost, user_id) VALUES
    (CURRENT_TIMESTAMP + INTERVAL '1 month', 10, 4),  
    (CURRENT_TIMESTAMP + INTERVAL '2 months', 15, 5), 
    (CURRENT_TIMESTAMP + INTERVAL '3 months', 20, 12),  
    (CURRENT_TIMESTAMP + INTERVAL '1 month', 10, 13),  
    (CURRENT_TIMESTAMP + INTERVAL '6 months', 50, 24);  

-- Sample data for auto_bid
INSERT INTO auto_bid (max, bidder, auction) VALUES
    (200, 5, 1);

-- Sample data for bid
INSERT INTO bid (value, bidder, auction) VALUES
    (60, 5, 1),
    (100, 19, 2),
    (150, 8, 2),
    (220, 12, 2),
    (300, 8, 2),
    (500, 5, 3),
    (290, 5, 9),
    (120, 13, 11),
    (200, 15, 11),
    (250, 21, 13),
    (800, 8, 14),
    (850, 5, 14),
    (7500, 4, 19),
    (7600, 8, 19),
    (7500, 8, 20),
    (7500, 8, 21),
    (7600, 4, 21),
    (7800, 8, 21),
    (10000, 24, 23),
    (11000, 25, 23),
    (11200, 24, 23),
    (12000, 25, 23),
    (12100, 24, 23),
    (12400, 25, 23),
    (14000, 24, 23),
    (14500, 25, 23),
    (15000, 24, 23),
    (15200, 25, 23);

-- Sample data for advertisement
INSERT INTO advertisement (start_time,end_time, cost, auction_id) VALUES
    (CURRENT_TIMESTAMP,CURRENT_TIMESTAMP + INTERVAL '1 week', 20, 1),
    (CURRENT_TIMESTAMP,CURRENT_TIMESTAMP + INTERVAL '2 weeks', 30, 2),
    (CURRENT_TIMESTAMP,CURRENT_TIMESTAMP + INTERVAL '3 days', 15, 7),
    (CURRENT_TIMESTAMP,CURRENT_TIMESTAMP + INTERVAL '10 days', 25, 9),
    (CURRENT_TIMESTAMP,CURRENT_TIMESTAMP + INTERVAL '5 days', 20, 14);

-- Sample data for message
INSERT INTO message (content, auction, general_user_id) VALUES
    ('What is the condition of this item?', 1, 5);

-- Sample data for notification
INSERT INTO notification (description, general_user_id, notification_type, auction) VALUES
    ('New bid placed on your auction.', 4, 'New Bid', 23),
    ('New bid placed on your auction.', 4, 'New Bid', 23),
    ('New bid placed on your auction.', 4, 'New Bid', 23),
    ('New bid placed on your auction.', 4, 'New Bid', 23);

-- Sample data for user_transaction
INSERT INTO user_transaction (amount, transaction_type, user_id, advertisement_id) VALUES
    (20, 'Advertisement', 4, 1);

-- Sample data for image

-- Site config

INSERT INTO site_config (id,subscribe_price_plan_a,subscribe_price_plan_b,subscribe_price_plan_c,ad_price, discounted_ad_price,minimal_bid_interval) VALUES
(1,0.65, 0.50, 0.40,0.20, 0.10,50);

--triggers


CREATE OR REPLACE FUNCTION finish_auction(auction_id INTEGER)
    RETURNS BOOLEAN
    LANGUAGE plpgsql
AS
$$
DECLARE
    winning_bid RECORD;
    auction_finished BOOLEAN := FALSE;  
    auction_seller INTEGER;
    notification_message TEXT;
    follower_id INTEGER;
BEGIN

    
    IF EXISTS (SELECT 1 FROM auction WHERE id = auction_id AND end_time <= CURRENT_TIMESTAMP AND auction_state = 'Active') THEN

		SELECT seller_id INTO auction_seller
        FROM auction
        WHERE id = auction_id;
		
        
        SELECT id, value, bidder
        INTO winning_bid
        FROM bid
        WHERE auction = auction_id
        ORDER BY value DESC
        LIMIT 1;

        
        IF FOUND THEN
			
            
            UPDATE auction
            SET auction_state = 'Finished'
            WHERE id = auction_id;

            auction_finished := TRUE;  

			INSERT INTO notification (description, general_user_id,notification_type, auction)
            VALUES ('The auction ended, you Won!', winning_bid.bidder,'Auction Closed'::notification_type_enum, auction_id);
			
        ELSE
            
            UPDATE auction
            SET auction_state = 'Finished without bids'
            WHERE id = auction_id;

            auction_finished := TRUE;  
        END IF;

        
        FOR follower_id IN
            SELECT f.follower
            FROM follow_auction f
            JOIN users u ON u.id = f.follower
            WHERE (f.auction = auction_id
            AND u.following_auction_closed_notifications = TRUE)
        LOOP           
            INSERT INTO notification (description, general_user_id,notification_type, auction)
            VALUES ('An auction you were following just ended', follower_id,'Auction Closed'::notification_type_enum, auction_id);

        END LOOP;

        
        INSERT INTO notification (description, general_user_id,notification_type, auction)
        VALUES ( 'One of your auctions just ended...', auction_seller,'Auction Closed'::notification_type_enum, auction_id);

	
	ELSIF EXISTS (SELECT 1 FROM auction WHERE id = auction_id AND (auction_state = 'Finished' OR auction_state = 'Finished without bids')) THEN
		auction_finished := TRUE;
		
	END IF;
	
    RETURN auction_finished;  
END;
$$;


CREATE OR REPLACE FUNCTION sendRatingNotification()
RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
DECLARE
    ratingNotification BOOLEAN;
    msg TEXT;
    newRate BOOLEAN;
BEGIN
    newRate := TG_ARGV[0]::BOOLEAN;

    SELECT rating_notifications INTO ratingNotification
    FROM users
    WHERE id = NEW.rated_user;

    IF ratingNotification THEN
        IF newRate THEN
            msg := 'You have been rated ' || NEW.rate || ' stars!';
        ELSE
			IF OLD.rate IS NOT DISTINCT FROM NEW.rate THEN
				RAISE NOTICE 'User already rated with % stars!', NEW.rate;
				RETURN NULL;
			END IF;
            msg := 'A rate changed from ' || OLD.rate || ' to ' || NEW.rate || ' stars!';
        END IF;

        INSERT INTO notification (description, general_user_id, notification_type, rate_user)
        VALUES (msg, NEW.rated_user, 'Rating'::notification_type_enum, NEW.id);
    END IF;

    RETURN NEW;
END;
$$;



CREATE OR REPLACE FUNCTION resetAuction(auction_id INTEGER, user_id INTEGER)
    RETURNS VOID
    LANGUAGE plpgsql
AS
$$
DECLARE
    seller_id INTEGER;
    follower_id INTEGER;
    bidder_id INTEGER;
BEGIN

    RAISE NOTICE '6 - AUCTION ID(962): %', auction_id;    
    RAISE NOTICE '6 - USER ID(963): %', user_id;    

    IF auction_id IS NOT NULL THEN

        SELECT a.seller_id INTO seller_id
        FROM auction a
        WHERE a.id = auction_id;
            
        RAISE NOTICE '7 - SELLER ID(971): %', seller_id;    

        -- Notify seller
        INSERT INTO notification (description, general_user_id, notification_type, auction)
        VALUES (
            'The bids for your auction have been reset due to a blocked or deleted user.',
            seller_id,
            'New Auction'::notification_type_enum,
            auction_id
        );

        -- Notify bidders
        FOR bidder_id IN
            SELECT DISTINCT bidder
            FROM bid b
            WHERE b.auction = auction_id AND b.bidder <> user_id
        LOOP
            RAISE NOTICE '8 - BIDDER ID(988): %', bidder_id;   

            INSERT INTO notification (description, general_user_id, notification_type, auction)
            VALUES (
                'An auction that you have participated has been reset.',
                bidder_id,
                'New Auction'::notification_type_enum,
                auction_id
            );
        END LOOP;

        -- Notify followers
        FOR follower_id IN
            SELECT f.follower
            FROM follow_auction f
            JOIN users u ON u.id = f.follower
            WHERE f.auction = auction_id AND f.follower <> user_id
        LOOP
            RAISE NOTICE '9 - FOLLOWER ID(1006): %', follower_id;   

            INSERT INTO notification (description, general_user_id, notification_type, auction)
            VALUES (
                'An auction you follow has been reset.',
                follower_id,
                'New Auction'::notification_type_enum,
                auction_id
            );
        END LOOP;

        DELETE FROM bid b
        WHERE b.auction = auction_id;

        DELETE FROM auto_bid ab
        WHERE ab.auction = auction_id;
    END IF;
END;
$$;

CREATE OR REPLACE FUNCTION blocked_user()
    RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
DECLARE
    auction_seller_id INTEGER;
    auction_id INTEGER;
    highest_bid_value NUMERIC;
BEGIN

    IF NEW.blocked = TRUE THEN 
        -- Update auctions for the blocked seller
        UPDATE auction
        SET auction_state = 'Canceled'
        WHERE auction.seller_id = OLD.id AND auction.auction_state <> 'Shipped';

        -- Handle bids made by the blocked user
        FOR auction_id, highest_bid_value IN
            SELECT b.auction, b.value
            FROM bid b
            JOIN auction a ON b.auction = a.id
            WHERE b.bidder = OLD.id
            AND (a.auction_state = 'Active' OR a.auction_state = 'Finished')
            AND b.value = (
                SELECT MAX(value)
                FROM bid
                WHERE bid.auction = b.auction
            )
        LOOP
            -- If auction is 'Active'
            IF (SELECT auction.auction_state FROM auction WHERE auction.id = auction_id) = 'Active' THEN
                PERFORM free_bid_money(OLD.id, highest_bid_value, auction_id);
                PERFORM resetAuction(auction_id, OLD.id);
            END IF;

            -- If auction is 'Finished'
            IF (SELECT auction.auction_state FROM auction WHERE auction.id = auction_id) = 'Finished' THEN
                UPDATE auction
                SET auction_state = 'Canceled'
                WHERE auction.id = auction_id;

                SELECT auction.seller_id INTO auction_seller_id
                FROM auction
                WHERE auction.id = auction_id;

                -- Notify the seller
                INSERT INTO notification (description, general_user_id, notification_type, auction)
                VALUES (
                    'The buyer for your product has been blocked. Please relist the product.',
                    auction_seller_id,
                    'Auction Canceled'::notification_type_enum,
                    auction_id
                );
            END IF;
        END LOOP;
    END IF;

    RETURN NULL;
END;
$$;

CREATE TRIGGER after_block_update_trigger
AFTER UPDATE OF blocked ON users
FOR EACH ROW
EXECUTE FUNCTION blocked_user();



CREATE OR REPLACE FUNCTION delete_user()
    RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
DECLARE
    auction_id INTEGER;
    highest_bid_value NUMERIC;
    seller_id INTEGER;
BEGIN
    RAISE NOTICE '1 - ROLE(1065): %', OLD.role;
    IF OLD.role = 'Regular User' THEN

        DELETE FROM auction a
        WHERE a.seller_id = OLD.id;

        FOR auction_id, highest_bid_value IN
            SELECT b.auction, b.value
            FROM bid b
            JOIN auction a ON b.auction = a.id
            WHERE b.bidder = OLD.id
            AND (a.auction_state = 'Active' OR a.auction_state = 'Finished')
            AND b.value = (
                SELECT MAX(value)
                FROM bid
                WHERE auction = b.auction
            )
        LOOP
            RAISE NOTICE '2 - AUCTION ID(1080): %', auction_id;
            RAISE NOTICE '2 - HIGHEST BID(1081): %', highest_bid_value;


            IF (SELECT a.auction_state FROM auction a WHERE a.id = auction_id) = 'Active' THEN
                PERFORM free_bid_money(OLD.id, highest_bid_value, auction_id);
                PERFORM resetAuction(auction_id, OLD.id);
            END IF;

            IF (SELECT a.auction_state FROM auction a WHERE a.id = auction_id) = 'Finished' THEN

                UPDATE auction
                SET auction_state = 'Canceled'
                WHERE id = auction_id;

                SELECT a.seller_id INTO seller_id
                FROM auction a
                WHERE a.id = auction_id;

                RAISE NOTICE '10 - SELLER ID(1105): %', seller_id;   

                -- Notify seller
                INSERT INTO notification (description, general_user_id, notification_type, auction)
                VALUES (
                    'The buyer for your product has been deleted. Please relist the product.',
                    seller_id,
                    'Auction Canceled'::notification_type_enum,
                    auction_id
                );
            END IF;
        END LOOP;

        UPDATE bid 
        SET bidder = 1
        WHERE bidder = OLD.id;

    ELSIF OLD.role = 'Expert' THEN
        UPDATE auction
        SET expert = 3
        WHERE expert = OLD.id;

    ELSIF OLD.role = 'Admin' THEN
        UPDATE block
        SET appeal_admin = 2
        WHERE appeal_admin = OLD.id;
    END IF;

    RETURN OLD;
END;
$$;

CREATE TRIGGER after_delete_user
BEFORE DELETE ON general_user
FOR EACH ROW
EXECUTE FUNCTION delete_user();


CREATE OR REPLACE FUNCTION set_user_blocked()
    RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
BEGIN

    
    UPDATE users
    SET blocked = TRUE
    WHERE id = NEW.blocked_user;

    
    INSERT INTO notification (description, general_user_id, notification_type, block)
    VALUES ('You have been blocked', NEW.blocked_user, 'Blocked'::notification_type_enum, NEW.id);

    RETURN NULL;
END;
$$;

CREATE TRIGGER after_block_insert_trigger
AFTER INSERT ON block
FOR EACH ROW
EXECUTE FUNCTION set_user_blocked();


CREATE OR REPLACE FUNCTION update_user_blocked()
    RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
BEGIN
    
    IF NEW.appeal_accepted = TRUE AND NEW.end_time > CURRENT_TIMESTAMP THEN
        UPDATE users
        SET blocked = FALSE
        WHERE id = NEW.blocked_user;

        UPDATE block
        SET end_time = NOW()
        WHERE id = NEW.id;

        DELETE FROM report_user
        WHERE reported = NEW.blocked_user;


        IF OLD.appeal_message IS NOT NULL AND OLD.appeal_accepted IS NULL THEN
            INSERT INTO notification (description, general_user_id,notification_type, block)
            VALUES ( 'Your appeal has been accepted!', NEW.blocked_user, 'Unblocked'::notification_type_enum, NEW.id);
        END IF;
    END IF;

    
    IF NEW.appeal_accepted = FALSE AND NEW.end_time > CURRENT_TIMESTAMP THEN
        INSERT INTO notification (description, general_user_id,notification_type, block)
        VALUES ( 'Your appeal has been rejected!', NEW.blocked_user, 'Blocked'::notification_type_enum, NEW.id);
    END IF;

    RETURN NEW;
END;
$$;

CREATE TRIGGER after_block_update_trigger
AFTER UPDATE OF appeal_accepted ON block
FOR EACH ROW
EXECUTE FUNCTION update_user_blocked();


CREATE OR REPLACE FUNCTION update_user_wallet()
    RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
BEGIN
    
    IF NEW.transaction_type = 'Auction' THEN
        
        UPDATE users
        SET wallet = wallet - NEW.amount
        WHERE id = NEW.user_id;

        UPDATE users
        SET wallet = wallet + NEW.amount,
			available_balance = available_balance + NEW.amount
        WHERE id = NEW.seller_id;

    ELSIF NEW.transaction_type = 'Advertisement' THEN
        
        UPDATE users
        SET wallet = wallet - NEW.amount
        WHERE id = NEW.user_id;

    ELSIF NEW.transaction_type = 'Subscription' THEN
        
        UPDATE users
        SET wallet = wallet - NEW.amount
        WHERE id = NEW.user_id;

    ELSIF NEW.transaction_type = 'Wallet' THEN
        
        UPDATE users
        SET wallet = wallet + NEW.amount,
            available_balance = available_balance + NEW.amount
        WHERE id = NEW.user_id;
    END IF;

    RETURN NEW;
END;
$$;

CREATE TRIGGER after_transaction_insert_trigger
AFTER INSERT ON user_transaction
FOR EACH ROW
EXECUTE FUNCTION update_user_wallet();


CREATE OR REPLACE FUNCTION check_user_wallet_before_wallet_transaction_insert()
    RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
DECLARE
    available_balance_r NUMERIC;
BEGIN
    
    SELECT available_balance INTO available_balance_r
    FROM users
    WHERE id = NEW.user_id;

    
    IF available_balance_r >= (- NEW.amount) THEN
        RETURN NEW; 
    ELSE
        
        RAISE EXCEPTION 'Insufficient funds for user ID %: Attempted to withdraw from wallet %.2f, but available balance is %.2f',
            NEW.user_id, NEW.amount, available_balance_r;
    END IF;
END;
$$;

CREATE TRIGGER before_wallet_transaction_insert_trigger
BEFORE INSERT ON user_transaction
FOR EACH ROW
WHEN (NEW.transaction_type = 'Wallet')
EXECUTE FUNCTION check_user_wallet_before_wallet_transaction_insert();


CREATE OR REPLACE FUNCTION handle_before_advertisement_insert()
    RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
DECLARE
    user_id INTEGER;
    available_balance_r NUMERIC;

    auction_finished BOOLEAN;
BEGIN
	
    
    auction_finished := finish_auction(NEW.auction_id);


    
    IF NOT auction_finished THEN

        
        IF NOT EXISTS (SELECT 1 FROM auction WHERE id = NEW.auction_id AND auction_state = 'Active') THEN
            RAISE NOTICE 'Cannot advertise auction unless the auction is active.';
			RETURN NULL;
        END IF;

        

        
        SELECT auction.seller_id INTO user_id
        FROM auction
        WHERE id = NEW.auction_id;

        
        SELECT available_balance INTO available_balance_r
        FROM users
        WHERE id = user_id;

        
        IF available_balance_r >= NEW.cost THEN
            
            UPDATE users
            SET available_balance = available_balance - NEW.cost
            WHERE id = user_id;

            RETURN NEW; 
        ELSE
            
            RAISE NOTICE 'Insufficient funds for user ID %: Attempted to create advertisement costing %.2f, but available balance is %.2f',
                user_id, NEW.cost, available_balance_r;
			RETURN NULL;
        END IF;
	ELSE
		RAISE NOTICE 'Auction ID % has already finished.',
        NEW.auction_id;
		RETURN NULL;
	END IF;
END;
$$;

CREATE TRIGGER before_advertisement_insert_trigger
BEFORE INSERT ON advertisement
FOR EACH ROW
EXECUTE FUNCTION handle_before_advertisement_insert();


CREATE OR REPLACE FUNCTION handle_after_advertisement_insert()
    RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
DECLARE
    user_id INTEGER;
BEGIN
    
    UPDATE auction
    SET advertised = TRUE
    WHERE id = NEW.auction_id;

    
    SELECT auction.seller_id INTO user_id
    FROM auction
    WHERE id = NEW.auction_id;

    
    INSERT INTO user_transaction (amount, transaction_type, user_id, advertisement_id)
    VALUES (NEW.cost, 'Advertisement'::transaction_type_enum, user_id, NEW.id);

    RETURN NEW;
END;
$$;

CREATE TRIGGER after_advertisement_insert_trigger
AFTER INSERT ON advertisement
FOR EACH ROW
EXECUTE FUNCTION handle_after_advertisement_insert();


CREATE OR REPLACE FUNCTION check_user_wallet_before_subscription_insert()
    RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
DECLARE
    available_balance_r NUMERIC;

BEGIN
	
	SELECT available_balance INTO available_balance_r
	FROM users
	WHERE id = NEW.user_id;
	
    
    IF available_balance_r >= NEW.cost THEN
        
        UPDATE users
        SET available_balance = available_balance - NEW.cost
        WHERE id = NEW.user_id;

        RETURN NEW; 
    ELSE
        
        RAISE EXCEPTION 'Insufficient funds for user ID %: Attempted to create subscription costing %, but available balance is %',
            NEW.user_id, NEW.cost, available_balance_r;
    END IF;
END;
$$;

CREATE TRIGGER before_subscription_insert_trigger
BEFORE INSERT ON subscription
FOR EACH ROW
EXECUTE FUNCTION check_user_wallet_before_subscription_insert();



CREATE OR REPLACE FUNCTION handle_subscription_update()
    RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
DECLARE
	is_subscribed Bool;

    extraMoney NUMERIC;
BEGIN

    -- Get money diff
    extraMoney = NEW.cost - OLD.cost;
    -- Time extension is considered to be correct


    -- User should be subscribed to do the extension
	SELECT subscribed INTO is_subscribed
	FROM users
	WHERE id = NEW.user_id;
	IF NOT is_subscribed THEN
	    RAISE EXCEPTION 'User should be already subscribed!';
    END IF;

    -- Update users balance
    UPDATE users
    SET available_balance = available_balance - extraMoney
    WHERE id = NEW.user_id;

    -- Create associated transaction
    INSERT INTO user_transaction (amount, transaction_type, user_id, subscription_id)
    VALUES (extraMoney, 'Subscription'::transaction_type_enum, NEW.user_id, NEW.id);

    RETURN NEW; 
        
END;
$$;

CREATE TRIGGER after_subscription_update_trigger
AFTER UPDATE ON subscription
FOR EACH ROW
EXECUTE FUNCTION handle_subscription_update();




CREATE OR REPLACE FUNCTION handle_after_subscription_insert()
    RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
BEGIN
    
    UPDATE users
    SET subscribed = TRUE
    WHERE id = NEW.user_id;

    
    INSERT INTO user_transaction (amount, transaction_type, user_id, subscription_id)
    VALUES (NEW.cost, 'Subscription'::transaction_type_enum, NEW.user_id, NEW.id);

    RETURN NEW;
END;
$$;

CREATE TRIGGER after_subscription_insert_trigger
AFTER INSERT ON subscription
FOR EACH ROW
EXECUTE FUNCTION handle_after_subscription_insert();



CREATE OR REPLACE FUNCTION handle_bid_insert()
    RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
DECLARE
    available_balance_r NUMERIC;
    current_highest_bid RECORD;
    state auction_state_enum;
    min_bid NUMERIC;
    bid_interval NUMERIC;
    auction_finished BOOLEAN;
    autobid_exist INTEGER;
    current_highest_bidder_autobid_exist INTEGER;
BEGIN
    
    auction_finished := finish_auction(NEW.auction);

    
    IF NOT auction_finished THEN
        
        SELECT auction_state, minimum_bid INTO state, min_bid
        FROM auction
        WHERE id = NEW.auction;

        IF FOUND AND state = 'Active' THEN
		
            SELECT minimal_bid_interval INTO bid_interval
            FROM site_config
            WHERE id = 1;
			
			
            SELECT id, value, bidder
            INTO current_highest_bid
            FROM bid
            WHERE auction = NEW.auction
            ORDER BY value DESC
            LIMIT 1
            FOR UPDATE;

            
            IF FOUND AND (NEW.value < current_highest_bid.value + bid_interval) THEN
                RAISE NOTICE 'Invalid bid made by user ID %: Attempted to bid %.2f, but bid need to be at least %.2f',
                    NEW.bidder, NEW.value, current_highest_bid.value + bid_interval;
				RETURN NULL;
            END IF;

            IF min_bid > NEW.value THEN
                RAISE NOTICE 'Invalid bid made by user ID %: Attempted to bid %.2f, but minimum acceptable bid is %.2f',
                    NEW.bidder, NEW.value, min_bid;
				RETURN NULL;
            END IF;

            SELECT id INTO autobid_exist
            FROM auto_bid
            WHERE bidder = NEW.bidder AND auction = NEW.auction AND active = TRUE
            LIMIT 1;

            IF autobid_exist IS NULL THEN
            
                SELECT available_balance INTO available_balance_r
                FROM users
                WHERE id = NEW.bidder;

                IF available_balance_r < NEW.value THEN
                    RAISE NOTICE 'Insufficient funds for user ID %: Attempted to bid %.2f, but available balance is %.2f',
                        NEW.bidder, NEW.value, available_balance_r;
                    RETURN NULL;
                END IF;
                
                UPDATE users
                SET available_balance = available_balance - NEW.value
                WHERE id = NEW.bidder;

            END IF;

            IF current_highest_bid.bidder <> NEW.bidder THEN

                SELECT id INTO current_highest_bidder_autobid_exist
                FROM auto_bid
                WHERE bidder = current_highest_bid.bidder AND auction = NEW.auction AND active = TRUE
                LIMIT 1;

                IF current_highest_bidder_autobid_exist IS NULL THEN 

                    UPDATE users
                    SET available_balance = available_balance + current_highest_bid.value
                    WHERE id = current_highest_bid.bidder;
                    
                END IF;
            END IF;
            

            RETURN NEW;
        ELSE
            RAISE NOTICE 'Auction is not active ID %: User ID % Attempted to bid %.2f.',
                NEW.auction, NEW.bidder, NEW.value;
			RETURN NULL;
        END IF;
    ELSE
        RAISE NOTICE 'Auction ID % has already finished. User ID % attempted to bid %.2f.',
            NEW.auction, NEW.bidder, NEW.value;
		RETURN NULL;
    END IF;
END;
$$;

CREATE TRIGGER before_bid_insert_trigger
BEFORE INSERT ON bid
FOR EACH ROW
EXECUTE FUNCTION handle_bid_insert();


CREATE OR REPLACE FUNCTION after_bid_insert_notification()
    RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
DECLARE
    follower_id INT;
    seller INT;
    end_time_r TIMESTAMP;
BEGIN

    SELECT auction.end_time INTO end_time_r
    FROM auction
    WHERE id = NEW.auction;


    IF end_time_r > CURRENT_TIMESTAMP - INTERVAL '15 minutes' THEN
        UPDATE auction
        SET end_time = end_time_r + INTERVAL '30 minutes'
        WHERE id = NEW.auction;
    END IF;


    
    FOR follower_id IN
        SELECT f.follower
        FROM follow_auction f
        JOIN users u ON u.id = f.follower
        WHERE f.auction = NEW.auction
        AND u.following_auction_new_bid_notifications = TRUE AND u.id <> NEW.bidder
    LOOP
        
        INSERT INTO notification (description, general_user_id, notification_type, auction, bid)
            VALUES ('A new bid was made in a auction you follow', follower_id, 'New Bid'::notification_type_enum, New.auction, New.id);
    END LOOP;	
    
    SELECT seller_id INTO seller
    FROM auction a
    JOIN users u ON u.id = a.seller_id
    WHERE a.id = New.auction
    AND u.seller_auction_ending_notifications = TRUE;

    IF FOUND THEN
        INSERT INTO notification (description, general_user_id, notification_type, auction, bid)
            VALUES ('New bid in one of your auctions!', seller, 'New Bid'::notification_type_enum, New.auction, New.id);
    END IF;


    RETURN NEW;
END;
$$;

CREATE TRIGGER after_bid_insert_notification_trigger
AFTER INSERT ON bid
FOR EACH ROW
EXECUTE FUNCTION after_bid_insert_notification();


CREATE OR REPLACE FUNCTION handle_auto_bid()
    RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
DECLARE
    auto_bid_record RECORD;
    current_highest_bid RECORD;
	top_two_auto_bids RECORD;

    first_auto_bid auto_bid%ROWTYPE;
    second_auto_bid auto_bid%ROWTYPE;

	found_first_bidder BOOLEAN = FALSE;
    auto_bid_removed BOOLEAN = FALSE;

    bid_interval NUMERIC;
    new_bid_value NUMERIC;

    old_max_bidder INT;
BEGIN

	SELECT bidder
	INTO old_max_bidder
	FROM bid
	WHERE auction = NEW.auction
	ORDER BY value DESC
	OFFSET 1
	LIMIT 1
	FOR UPDATE;

	IF FOUND THEN
    
	    INSERT INTO notification (description, general_user_id, notification_type, auction, bid)
	    VALUES ('A bid was made higher than yours!', old_max_bidder, 'New Bid'::notification_type_enum, New.auction, New.id);
	END IF;

    SELECT minimal_bid_interval INTO bid_interval
    FROM site_config
    WHERE id = 1;

	FOR top_two_auto_bids IN 
        SELECT * 
        FROM auto_bid
        WHERE auction = NEW.auction
          AND active = TRUE
        ORDER BY max DESC, timestamp ASC
        LIMIT 2
    LOOP
	    IF top_two_auto_bids IS NOT NULL THEN
			IF found_first_bidder THEN
				second_auto_bid := top_two_auto_bids;
			ELSE 
				first_auto_bid := top_two_auto_bids;
				found_first_bidder = True;
			END IF;
		ELSE 
			EXIT;
		END IF;
	END LOOP;

    SELECT id, value, bidder
    INTO current_highest_bid
    FROM bid
    WHERE auction = NEW.auction
    ORDER BY value DESC
    LIMIT 1;

	IF second_auto_bid IS NOT NULL THEN
			
		IF second_auto_bid.max = first_auto_bid.max THEN
	    	new_bid_value := second_auto_bid.max;
            
		ELSE
			new_bid_value := LEAST(second_auto_bid.max + bid_interval, first_auto_bid.max);
		END IF;
	
		UPDATE auto_bid
		SET active = FALSE
		WHERE id = second_auto_bid.id;

        IF (first_auto_bid.bidder <> NEW.bidder) AND (current_highest_bid.value = new_bid_value) THEN
            UPDATE users
            SET available_balance = available_balance + new_bid_value
            WHERE id = first_auto_bid.bidder;

            INSERT INTO notification (description, general_user_id, notification_type, auction)
            VALUES ('Your auto-bid of maximum ' || first_auto_bid.max || '€ just got surpassed', first_auto_bid.bidder, 'Auto Bid'::notification_type_enum, NEW.auction);
            

            INSERT INTO notification (description, general_user_id, notification_type, auction)
            VALUES ('Your auto-bid of maximum ' || second_auto_bid.max || '€ has no more potential, but still winning', second_auto_bid.bidder, 'Auto Bid'::notification_type_enum, NEW.auction);

        ELSE
            UPDATE users
            SET available_balance = available_balance + second_auto_bid.max
            WHERE id = second_auto_bid.bidder;
            
            INSERT INTO notification (description, general_user_id, notification_type, auction)
            VALUES ('Your auto-bid of maximum ' || second_auto_bid.max || '€ just got surpassed', second_auto_bid.bidder, 'Auto Bid'::notification_type_enum, NEW.auction);

        END IF;

    ELSE
        IF first_auto_bid IS NOT NULL AND first_auto_bid.bidder = NEW.bidder THEN
            new_bid_value = NEW.value;
        ELSE  
		    new_bid_value := NEW.value + bid_interval;
        END IF;
	END IF;



    IF first_auto_bid IS NOT NULL THEN

        IF (first_auto_bid.max < new_bid_value) THEN

            UPDATE auto_bid
            SET active = FALSE
            WHERE id = first_auto_bid.id;
            
            UPDATE users
            SET available_balance = available_balance + first_auto_bid.max
            WHERE id = first_auto_bid.bidder;
            
            
            INSERT INTO notification (description, general_user_id, notification_type, auction)
                    VALUES ('Your auto-bid of maximum ' || first_auto_bid.max || '€ just got surpassed', first_auto_bid.bidder, 'Auto Bid'::notification_type_enum, NEW.auction);
        ELSE

            IF (first_auto_bid.max < new_bid_value + bid_interval) THEN    

                UPDATE auto_bid
                SET active = FALSE
                WHERE id = first_auto_bid.id;

                UPDATE users
                SET available_balance = available_balance + (first_auto_bid.max - new_bid_value)
                WHERE id = first_auto_bid.bidder;

                auto_bid_removed = TRUE;

                INSERT INTO notification (description, general_user_id, notification_type, auction)
                    VALUES ('Your auto-bid of maximum ' || first_auto_bid.max || '€ has no more potential, but still winning', first_auto_bid.bidder, 'Auto Bid'::notification_type_enum, NEW.auction);
      
            END IF;    

            IF (first_auto_bid.bidder <> NEW.bidder) THEN

                IF NOT (current_highest_bid.value = new_bid_value )THEN 

                    IF auto_bid_removed THEN
                        UPDATE users
                        SET available_balance = available_balance + new_bid_value
                        WHERE id = first_auto_bid.bidder;
                    END IF;

                    INSERT INTO bid (value, bidder, auction)
                        VALUES (new_bid_value, first_auto_bid.bidder, NEW.auction);
                END IF;

            ELSIF second_auto_bid IS NOT NULL THEN
                
                UPDATE bid 
                SET value = new_bid_value
                WHERE id = NEW.id;

            END IF;
        END IF;
    END IF;

    RETURN NEW;
END;
$$;

CREATE TRIGGER after_bid_insert_trigger
AFTER INSERT ON bid
FOR EACH ROW
EXECUTE FUNCTION handle_auto_bid();



CREATE OR REPLACE FUNCTION delete_autobid()
    RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
DECLARE
    current_highest_bid RECORD;
BEGIN

    SELECT id, value, bidder
    INTO current_highest_bid
    FROM bid
    WHERE auction = OLD.auction
    ORDER BY value DESC
    LIMIT 1;

    IF current_highest_bid.bidder = OLD.bidder THEN
        PERFORM retrieve_notspendedmoney(OLD.bidder, current_highest_bid.value, OLD.auction);
    END IF;

    RETURN OLD;

END;
$$;

CREATE TRIGGER delete_autobid
BEFORE DELETE ON auto_bid
FOR EACH ROW
EXECUTE FUNCTION delete_autobid();


CREATE OR REPLACE FUNCTION verify_auto_bid()
    RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
DECLARE
    user_id INTEGER;
    available_balance_r NUMERIC;
    auction_finished BOOLEAN;
    highest_bid_value INTEGER;
    bid_interval NUMERIC;
    min_bid NUMERIC;
    current_highest_bid RECORD;
BEGIN

    
    auction_finished := finish_auction(NEW.auction);

    
    IF NOT auction_finished THEN

        
        IF NOT EXISTS (SELECT 1 FROM auction WHERE id = NEW.auction AND auction_state = 'Active') THEN
            RAISE NOTICE 'Cannot create autoBidder unless the auction is active.';
        	RETURN NULL;
		END IF;

        DELETE FROM auto_bid
        WHERE active = true AND bidder = NEW.bidder AND auction = NEW.auction;    

        SELECT minimal_bid_interval INTO bid_interval
        FROM site_config
        WHERE id = 1;

        SELECT minimum_bid
        INTO min_bid
        FROM auction
        WHERE id = NEW.auction;

        SELECT id, value, bidder
        INTO current_highest_bid
        FROM bid
        WHERE auction = NEW.auction
        ORDER BY value DESC
        LIMIT 1;
        
        IF FOUND THEN
            IF (NEW.max < current_highest_bid.value + bid_interval) THEN
            RAISE NOTICE 'Max value of bid is too low, it needs to be at least %.2f', (current_highest_bid.value + bid_interval);
        	RETURN NULL;
            END IF;

        ELSE 
            IF min_bid > NEW.max THEN
                RAISE NOTICE 'Invalid bid made by user ID %: Attempted to bid %.2f, but minimum acceptable bid is %.2f',
                    NEW.bidder, NEW.value, min_bid;
                RETURN NULL;
            END IF;
		END IF;

        SELECT available_balance INTO available_balance_r
        FROM users
        WHERE id = NEW.bidder;

        IF current_highest_bid.bidder = NEW.bidder THEN
            IF available_balance_r >= (NEW.max - current_highest_bid.value) THEN
                UPDATE users
                SET available_balance = available_balance - (NEW.max - current_highest_bid.value)
                WHERE id = NEW.bidder;

                RETURN NEW; 
            ELSE
                RAISE NOTICE 'Insufficient funds for user ID %: Attempted to create auto-bid with max %.2f, but available balance is %.2f',
                    NEW.bidder, NEW.max, available_balance_r;
                RETURN NULL;
		    END IF;
        ELSE
            IF available_balance_r >= NEW.max THEN
                UPDATE users
                SET available_balance = available_balance - NEW.max
                WHERE id = NEW.bidder;

                RETURN NEW; 
            ELSE
                RAISE NOTICE 'Insufficient funds for user ID %: Attempted to create auto-bid with max %.2f, but available balance is %.2f',
                    NEW.bidder, NEW.max, available_balance_r;
                RETURN NULL;
		    END IF;
        END IF;

    ELSE
        RAISE NOTICE 'Auction ID % has already finished.', NEW.auction;
		RETURN NULL;
    END IF;

END;
$$;

CREATE TRIGGER before_auto_bid_insert_trigger
BEFORE INSERT ON auto_bid
FOR EACH ROW
EXECUTE FUNCTION verify_auto_bid();




CREATE OR REPLACE FUNCTION create_auto_bid_first_bid()
    RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
DECLARE
    highest_bid_value NUMERIC;
    min_bid NUMERIC;
    bid_interval NUMERIC;
    available_balance_r NUMERIC;
    current_highest_bid RECORD;
BEGIN


    SELECT id, value, bidder
    INTO current_highest_bid
    FROM bid
    WHERE auction = NEW.auction
    ORDER BY value DESC
    LIMIT 1;

    IF current_highest_bid IS NULL THEN
        SELECT minimum_bid
        INTO min_bid
        FROM auction
        WHERE id = NEW.auction;

        INSERT INTO bid (value, bidder, auction)
        VALUES (min_bid, NEW.bidder, NEW.auction);

    ELSE
        
        SELECT minimal_bid_interval INTO bid_interval
        FROM site_config
        WHERE id = 1;

        IF current_highest_bid.bidder <> NEW.bidder THEN
            
            INSERT INTO bid (value, bidder, auction)
            VALUES (current_highest_bid.value + bid_interval, NEW.bidder, NEW.auction);

        END IF;

    END IF;

    RETURN NEW;
END;
$$;

CREATE TRIGGER after_auto_bid_insert_trigger
AFTER INSERT ON auto_bid
FOR EACH ROW
EXECUTE FUNCTION create_auto_bid_first_bid();



CREATE OR REPLACE FUNCTION handle_rate_user_insert()
    RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
BEGIN
    
    IF EXISTS (
        SELECT 1
        FROM rate_user
        WHERE rated_user = NEW.rated_user AND rater_user = NEW.rater_user
    ) THEN
        
        UPDATE rate_user
        SET rate = NEW.rate,
            timestamp = CURRENT_TIMESTAMP
        WHERE rated_user = NEW.rated_user AND rater_user = NEW.rater_user;

        
        RETURN NULL;
    ELSE
        
        RETURN NEW;
    END IF;
END;
$$;

CREATE TRIGGER before_rate_user_insert
BEFORE INSERT ON rate_user
FOR EACH ROW
EXECUTE FUNCTION handle_rate_user_insert();






CREATE TRIGGER after_rated_user_insert_trigger
AFTER INSERT ON rate_user
FOR EACH ROW
EXECUTE FUNCTION sendRatingNotification('true');



CREATE OR REPLACE FUNCTION sendNewAuctionNotifications()
    RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
DECLARE
    follower_id INTEGER;
    sellerName TEXT;
BEGIN
    IF NEW.auction_state = 'Active' THEN
        
        SELECT username INTO sellerName
        FROM general_user
        WHERE id = NEW.seller_id;

        
        FOR follower_id IN
                SELECT follower_user
                FROM follow_user f
                JOIN users u ON u.id = f.follower_user
                WHERE followed_user = NEW.seller_id
                AND u.new_auction_notifications = TRUE
            LOOP
                INSERT INTO notification (description, general_user_id, notification_type, auction)
                VALUES (sellerName || ' just created a new auction!', follower_id, 'New Auction'::notification_type_enum, NEW.id);
            END LOOP;
    END IF;
    RETURN NEW;
END;
$$;

CREATE TRIGGER after_auction_insert_trigger
AFTER INSERT ON auction
FOR EACH ROW
EXECUTE FUNCTION sendNewAuctionNotifications();

CREATE OR REPLACE FUNCTION delete_auction()
    RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
DECLARE
    highest_bidder_id INTEGER;
    highest_bid_value NUMERIC;
	winning_bid_id INTEGER;
    follower_id INT;
BEGIN
        
    SELECT bidder, value
    INTO highest_bidder_id, highest_bid_value
    FROM bid
    WHERE auction = OLD.id
    ORDER BY value DESC
    LIMIT 1;

    RAISE NOTICE '20 - HIGHEST BIDDER ID: %, HIGHEST BID VAL: %', highest_bidder_id, highest_bid_value;


    IF OLD.auction_state = 'Finished' THEN
        PERFORM free_bid_money(highest_bidder_id, highest_bid_value, OLD.id);

        IF highest_bidder_id IS NOT NULL THEN
            RAISE NOTICE '21 - NOTIFY';
            INSERT INTO notification (description, general_user_id, notification_type)
            VALUES ('The auction that you won has been deleted due to the user being deleted.', highest_bidder_id, 'Auction Canceled'::notification_type_enum);
        END IF;

        DELETE FROM bid
        WHERE auction = OLD.id;

        RAISE NOTICE '22 - DELETE BIDS OF AUCTION';

    ELSIF OLD.auction_state = 'Active' THEN
        PERFORM free_bid_money(highest_bidder_id, highest_bid_value, OLD.id);
        FOR follower_id IN
            SELECT f.follower
            FROM follow_auction f
            JOIN users u ON u.id = f.follower
            WHERE f.auction = NEW.id
            AND u.following_auction_canceled_notifications = TRUE
        LOOP
            RAISE NOTICE '23 - FOLLOWER ID: %', follower_id;

            INSERT INTO notification (description, general_user_id, notification_type)
            VALUES ('An auction that you follow has been deleted', follower_id, 'Auction Canceled'::notification_type_enum);
        END LOOP;

        IF highest_bidder_id IS NOT NULL THEN
            RAISE NOTICE '24 - NOTIFY';

            INSERT INTO notification (description, general_user_id, notification_type)
            VALUES ('An auction that you were the highest bidder has been deleted', highest_bidder_id, 'Auction Canceled'::notification_type_enum);
        
            DELETE FROM bid
            WHERE auction = OLD.id;

            RAISE NOTICE '25 - DELETE BIDS OF AUCTION';
        END IF;
        

    ELSIF OLD.auction_state = 'Shipped' THEN

        UPDATE bid 
        SET auction = NULL
        WHERE auction = OLD.id;

        RAISE NOTICE '26 - SET DEFAULT BIDS OF MY AUCTION';

    END IF;

    RETURN OLD; 
END;
$$;

CREATE TRIGGER after_auction_delete_trigger
BEFORE DELETE ON auction
FOR EACH ROW
EXECUTE FUNCTION delete_auction();


CREATE OR REPLACE FUNCTION free_bid_money(highest_bidder_id INTEGER, highest_bid_value NUMERIC, auction_id INTEGER)
    RETURNS VOID
    LANGUAGE plpgsql
AS
$$
DECLARE
    auto_bid_value NUMERIC;
    money NUMERIC;
BEGIN
    RAISE NOTICE '3 - HIGHEST BIDDER ID(1974): %', highest_bidder_id;
    RAISE NOTICE '3 - HIGHEST BID VAL(1975): %', highest_bid_value;
    RAISE NOTICE '3 - AUCTION ID(1976): %', auction_id;    

    IF highest_bidder_id IS NOT NULL AND auction_id IS NOT NULL THEN
        SELECT max
        INTO auto_bid_value
        FROM auto_bid
        WHERE auction = auction_id AND bidder = highest_bidder_id AND active = TRUE;

        RAISE NOTICE '4 - AUTOBID VAL(1984): %', auto_bid_value;    

        IF auto_bid_value IS NOT NULL THEN 
            money = auto_bid_value;
        ELSE 
            money = highest_bid_value;
        END IF;

        RAISE NOTICE '5 - MONEY(1992): %', money;    

        UPDATE users
        SET available_balance = available_balance + money
        WHERE id = highest_bidder_id;
    END IF;
END;
$$;

CREATE OR REPLACE FUNCTION disable_autobids(auction_id INTEGER)
    RETURNS VOID
    LANGUAGE plpgsql
AS
$$
BEGIN
    IF auction_id IS NOT NULL THEN
        UPDATE auto_bid
        SET active = FALSE
        WHERE auction = auction_id;
    END IF;
END;
$$;

CREATE OR REPLACE FUNCTION retrieve_notspendedmoney(highest_bidder_id INTEGER, highest_bid_value NUMERIC, auction_id INTEGER)
    RETURNS VOID
    LANGUAGE plpgsql
AS
$$
DECLARE
    auto_bid_value NUMERIC;
BEGIN

    IF highest_bidder_id IS NOT NULL AND auction_id IS NOT NULL THEN
        SELECT max
        INTO auto_bid_value
        FROM auto_bid
        WHERE auction = auction_id AND bidder = highest_bidder_id AND active = TRUE;

        IF auto_bid_value IS NOT NULL AND auto_bid_value > highest_bid_value THEN 
            UPDATE users
            SET available_balance = available_balance + (auto_bid_value - highest_bid_value)
            WHERE id = highest_bidder_id;
        END IF;
    END IF;
END;
$$;


CREATE OR REPLACE FUNCTION check_auction_state()
    RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
DECLARE
    highest_bidder_id INTEGER;
    highest_bid_value NUMERIC;
	winning_bid_id INTEGER;
	auction_seller INTEGER;
    follower_id INT;
    sellerName TEXT;
    blocked BOOLEAN;
BEGIN

	
    
    SELECT bidder, value, id
    INTO highest_bidder_id, highest_bid_value, winning_bid_id
    FROM bid
    WHERE auction = OLD.id
    ORDER BY value DESC
    LIMIT 1;

	SELECT seller_id
	INTO auction_seller
	FROM auction
	WHERE id = NEW.id;

    
    IF NEW.auction_state = 'Shipped' THEN
        
        IF OLD.auction_state <> 'Finished' OR OLD.delivery_location IS NULL THEN
            RAISE NOTICE 'Cannot set auction state to "Shipped" unless it is "Finished" and delivery_location is not null.';
			RETURN NULL;
        ELSE
			
            INSERT INTO user_transaction (amount, transaction_type, winner_bid, seller_id, user_id)
            VALUES (highest_bid_value, 'Auction', winning_bid_id, auction_seller, highest_bidder_id);

			
            
            INSERT INTO notification (description, general_user_id, notification_type, auction)
            VALUES ('A new item has been shipped', highest_bidder_id, 'Shipping'::notification_type_enum, NEW.id);
			
        END IF;

    ELSIF NEW.auction_state = 'Canceled' THEN
        PERFORM free_bid_money(highest_bidder_id, highest_bid_value, OLD.id);

        IF OLD.auction_state = 'Finished' THEN

            SELECT u.blocked INTO blocked
            FROM users u 
            WHERE u.id = highest_bidder_id;

            IF NOT blocked THEN
                INSERT INTO notification (description, general_user_id, notification_type, auction)
                VALUES ('The auction that you won has been canceled due to the user being blocked.', highest_bidder_id, 'Auction Canceled'::notification_type_enum, NEW.id);
		    END IF;

        ELSIF OLD.auction_state = 'Active' THEN

            FOR follower_id IN
                SELECT f.follower
                FROM follow_auction f
                JOIN users u ON u.id = f.follower
                WHERE f.auction = NEW.id
                AND u.following_auction_canceled_notifications = TRUE
            LOOP
                INSERT INTO notification (description, general_user_id, notification_type, auction)
                VALUES ('An auction that you follow has been canceled', follower_id, 'Auction Canceled'::notification_type_enum, NEW.id);
            END LOOP;

            IF highest_bidder_id IS NOT NULL THEN
                
                INSERT INTO notification (description, general_user_id, notification_type, auction)
                VALUES ('An auction that you were the highest bidder has been canceled', highest_bidder_id, 'Auction Canceled'::notification_type_enum, NEW.id);
            END IF;
            
            INSERT INTO notification (description, general_user_id, notification_type, auction)
            VALUES ('An auction of yours has been canceled', NEW.seller_id, 'Auction Canceled'::notification_type_enum, NEW.id);

        END IF;

        DELETE FROM bid b
        WHERE b.auction = OLD.id;

        PERFORM disable_autobids(OLD.id);

    ELSIF NEW.auction_state = 'Finished' THEN

        PERFORM retrieve_notspendedmoney(highest_bidder_id, highest_bid_value, OLD.id);
        PERFORM disable_autobids(OLD.id);


    ELSIF NEW.auction_state = 'Active' THEN
        
        SELECT username INTO sellerName
        FROM general_user
        WHERE id = NEW.seller_id;

        FOR follower_id IN
            SELECT f.follower_user
            FROM follow_user f
            JOIN users u ON u.id = f.follower_user
            WHERE f.followed_user = NEW.seller
            AND u.new_auction_notifications = TRUE
        LOOP
            INSERT INTO notification (description, general_user_id, notification_type, auction)
            VALUES (sellerName || ' just created a new auction!', follower_id, 'New Auction'::notification_type_enum, NEW.id);
        END LOOP;

    END IF;

    RETURN NEW;
END;
$$;

CREATE TRIGGER auction_state_check_trigger
BEFORE UPDATE OF auction_state ON auction
FOR EACH ROW
EXECUTE FUNCTION check_auction_state();


CREATE OR REPLACE FUNCTION handle_auction_evaluation_insert()
    RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
DECLARE
    auction_finished BOOLEAN;
BEGIN
    
    auction_finished := finish_auction(NEW.id);

    
    IF NOT auction_finished THEN
        
        IF NOT (OLD.auction_state = 'Draft' OR OLD.auction_state = 'Active') THEN
            RAISE NOTICE 'Cannot evaluate auction unless the auction is active.';
			RETURN NULL;
        ELSIF (OLD.evaluation IS NOT NULL) THEN
            RAISE NOTICE 'Cannot evaluate auction already evaluated.';
			RETURN NULL;
        END IF;
        RETURN NEW;
    ELSE
        RAISE NOTICE 'Auction ID % has already finished. User ID % attempted to make an evaluation.',
            NEW.id, NEW.expert;
		RETURN NULL;
    END IF;
END;
$$;

CREATE TRIGGER before_auction_evaluation_insert_trigger
BEFORE UPDATE OF evaluation ON auction
FOR EACH ROW
EXECUTE FUNCTION handle_auction_evaluation_insert();



CREATE OR REPLACE FUNCTION after_auction_evaluation_insert()
    RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
BEGIN

    INSERT INTO notification (description, general_user_id, notification_type, auction)
    VALUES ('Your auction has been evaluated', NEW.seller_id, 'Evaluation'::notification_type_enum, NEW.id);

    RETURN NEW;
END;
$$;

CREATE TRIGGER after_auction_evaluation_insert_trigger
AFTER UPDATE OF evaluation ON auction
FOR EACH ROW
EXECUTE FUNCTION after_auction_evaluation_insert();



CREATE OR REPLACE FUNCTION handle_category_attributes_change()
	RETURNS TRIGGER
    LANGUAGE plpgsql
AS $$
DECLARE
    auctionInfo RECORD;
    attr_key TEXT;
    attr_value TEXT;
    attr_type TEXT;
    enum_options JSON;
BEGIN
    
    FOR auctionInfo IN 
        SELECT id, attribute_values
        FROM auction
        WHERE category_id = OLD.id  
    LOOP

        
        FOR attr_key, attr_value IN 
            SELECT key, value
            FROM json_each_text(auctionInfo.attribute_values::json) 
        LOOP
            
            attr_type := (
                SELECT category_attr->>'type' 
                FROM json_array_elements(NEW.attribute_list::json) AS category_attr
                WHERE category_attr->>'name' = attr_key  
                LIMIT 1
            );

            
            IF attr_type IS NOT NULL THEN
                
                IF attr_type = 'enum' THEN
                    
                    enum_options := (
                        SELECT category_attr->'options'
                        FROM json_array_elements(NEW.attribute_list::json) AS category_attr
                        WHERE category_attr->>'name' = attr_key
                        LIMIT 1
                    );

                    
                    IF NOT EXISTS (
                        SELECT 1
                        FROM json_array_elements_text(enum_options) AS option
                        WHERE option = attr_value
                    ) THEN
                        
                        UPDATE auction
                        SET attribute_values = attribute_values::jsonb - attr_key 
                        WHERE id = auctionInfo.id;
                    END IF;

                
                ELSIF attr_type = 'string' THEN
                    
                    IF attr_value IS NULL OR attr_value = '' THEN
                        
                        UPDATE auction
                        SET attribute_values = attribute_values::jsonb - attr_key 
                        WHERE id = auctionInfo.id;
                    END IF;

                
                ELSIF attr_type = 'int' THEN
                    
                    IF NOT attr_value ~ '^\d+$' THEN
                        
                        UPDATE auction
                        SET attribute_values = attribute_values::jsonb - attr_key 
                        WHERE id = auctionInfo.id;
                    END IF;

                
                ELSIF attr_type = 'float' THEN
                    
                    IF NOT attr_value ~ '^\d+(\.\d+)?$' THEN
                        
                        UPDATE auction
                        SET attribute_values = attribute_values::jsonb - attr_key 
                        WHERE id = auctionInfo.id;
                    END IF;
                END IF;

			
            END IF;

            
            IF NOT EXISTS (
                SELECT 1 
                FROM json_array_elements(NEW.attribute_list::json) AS category_attr
                WHERE category_attr->>'name' = attr_key
            ) THEN
                
                UPDATE auction
                SET attribute_values = attribute_values::jsonb - attr_key 
                WHERE id = auctionInfo.id;
            END IF;
        END LOOP;
    END LOOP;

    RETURN NEW;
END;
$$;


CREATE TRIGGER after_category_update
BEFORE UPDATE OF attribute_list ON category
FOR EACH ROW
EXECUTE FUNCTION handle_category_attributes_change();



CREATE OR REPLACE FUNCTION handle_message_insert()
    RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
DECLARE
    auction_finished BOOLEAN;
BEGIN
    
    auction_finished := finish_auction(NEW.auction);

    
    IF NOT auction_finished THEN
        
        IF NOT EXISTS (SELECT 1 FROM auction WHERE id = NEW.auction AND auction_state = 'Active') THEN
            RAISE NOTICE 'Cannot send a message unless the auction is active.';
			RETURN NULL;
        END IF;

        RETURN NEW;
    ELSE
        RAISE NOTICE 'Auction ID % has already finished. User ID % attempted to send message.',
            NEW.auction, NEW.general_user_id;
		RETURN NULL;
    END IF;
END;
$$;

CREATE TRIGGER before_message_insert_trigger
BEFORE INSERT ON message
FOR EACH ROW
EXECUTE FUNCTION handle_message_insert();




CREATE OR REPLACE FUNCTION after_message_insert_notification()
    RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
DECLARE
    follower_id INT;
    sellerID INT;
BEGIN
    
    FOR follower_id IN
        SELECT f.follower
        FROM follow_auction f
        JOIN users u ON u.id = f.follower
        WHERE f.auction = NEW.auction
          AND u.new_message_notifications = TRUE AND u.id <> NEW.general_user_id
    LOOP
        
        INSERT INTO notification (description, general_user_id, notification_type, auction)
        VALUES ('New message posted in an auction you follow', follower_id, 'New Message'::notification_type_enum, NEW.auction);
    END LOOP;

    
    SELECT seller_id INTO sellerID
    FROM auction
    WHERE id = NEW.auction;

    INSERT INTO notification (description, general_user_id, notification_type, auction)
    VALUES ('An auction of yours has received a new message', sellerID, 'New Message'::notification_type_enum, NEW.auction);

    RETURN NEW;
END;
$$;

CREATE TRIGGER after_message_insert_notification_trigger
AFTER INSERT ON message
FOR EACH ROW
EXECUTE FUNCTION after_message_insert_notification();



CREATE OR REPLACE FUNCTION handle_report_auction_insert()
    RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
DECLARE
    auction_finished BOOLEAN;
BEGIN
    
    auction_finished := finish_auction(NEW.auction);

    
    IF NOT auction_finished THEN
        
        IF NOT EXISTS (SELECT 1 FROM auction WHERE id = NEW.auction AND auction_state = 'Active') THEN
            RAISE NOTICE 'Cannot report auction unless the auction is active.';
			RETURN NULL;
        END IF;

        IF EXISTS (SELECT 1 FROM report_auction WHERE auction = NEW.auction AND reporter = NEW.reporter) THEN
            RAISE NOTICE 'Auction already reported.';
			RETURN NULL;
        END IF;

        RETURN NEW;
    ELSE
        RAISE NOTICE 'Auction ID % has already finished. User ID % attempted to report.',
            NEW.auction, NEW.reporter;
		RETURN NULL;
    END IF;
END;
$$;

CREATE TRIGGER before_report_auction_insert_trigger
BEFORE INSERT ON report_auction
FOR EACH ROW
EXECUTE FUNCTION handle_report_auction_insert();



CREATE OR REPLACE FUNCTION after_auction_reported_notification()
    RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
DECLARE
	sellerID INT;
BEGIN

	SELECT seller_id INTO sellerID
    FROM auction
    WHERE id = NEW.auction;

	INSERT INTO notification (description, general_user_id, notification_type, auction, report_auction)
    VALUES ('One of your auctions has been reported', sellerID, 'Auction Reported'::notification_type_enum, NEW.auction, NEW.id);
   
    RETURN NEW;
END;
$$;

CREATE TRIGGER after_auction_reported_notification_trigger
AFTER INSERT ON report_auction
FOR EACH ROW
EXECUTE FUNCTION after_auction_reported_notification();


CREATE OR REPLACE FUNCTION sendReportNotification()
    RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
BEGIN

    INSERT INTO notification (description, general_user_id, notification_type, report)
    VALUES ('You have been reported, behave well.', NEW.reported, 'User Reported'::notification_type_enum, NEW.id);

    RETURN NEW;

END;
$$;

CREATE TRIGGER after_report_user_insert_trigger
AFTER INSERT ON report_user
FOR EACH ROW
EXECUTE FUNCTION sendReportNotification();




CREATE OR REPLACE FUNCTION handle_category_delete()
    RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
BEGIN
    
    UPDATE auction
    SET category_id = 1,  
		attribute_values = '{}'
    WHERE category_id = OLD.id;

    DELETE FROM expert_category
    WHERE category_id = OLD.id;

    RETURN OLD; 
END;
$$;

CREATE TRIGGER before_category_delete_trigger
BEFORE DELETE ON category
FOR EACH ROW
EXECUTE FUNCTION handle_category_delete();



CREATE OR REPLACE FUNCTION send_follow_user_notification()
    RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
BEGIN

    
    INSERT INTO notification (description, general_user_id, notification_type, follower_id)
    VALUES ('You have a new follower!', NEW.followed_user, 'User Follow'::notification_type_enum, NEW.follower_user);

    RETURN NEW;
END;
$$;

CREATE TRIGGER after_follow_user_trigger
AFTER INSERT ON follow_user
FOR EACH ROW
EXECUTE FUNCTION send_follow_user_notification();


CREATE OR REPLACE FUNCTION send_follow_auction_notification()
    RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
DECLARE
    auction_seller INTEGER;
BEGIN
    SELECT seller_id into auction_seller
    from auction
    where id = NEW.auction;


    INSERT INTO notification (description, general_user_id, notification_type, auction)
    VALUES ('Your auction has a new follower!', auction_seller , 'Auction Follow'::notification_type_enum, NEW.auction);

    RETURN NEW;
END;
$$;

CREATE TRIGGER after_follow_auction_trigger
AFTER INSERT ON follow_auction
FOR EACH ROW
EXECUTE FUNCTION send_follow_auction_notification();


CREATE OR REPLACE FUNCTION send_unfollow_user_notification()
    RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
BEGIN

    IF NEW.followed_user IS NOT NULL THEN

        INSERT INTO notification (description, general_user_id, notification_type)
        VALUES ('You lost a follower!', OLD.followed_user, 'User Follow'::notification_type_enum);
    END IF;

    RETURN NULL;
END;
$$;

CREATE TRIGGER after_unfollow_user_trigger
AFTER DELETE ON follow_user
FOR EACH ROW
EXECUTE FUNCTION send_unfollow_user_notification();





CREATE TRIGGER after_rated_user_update_trigger
AFTER UPDATE OF rate ON rate_user
FOR EACH ROW
EXECUTE FUNCTION sendRatingNotification('false');






CREATE OR REPLACE FUNCTION unblock_user(user_id_r INTEGER)
    RETURNS VOID
    LANGUAGE plpgsql
AS
$$
DECLARE
    rows_updated INTEGER;
BEGIN
    
    UPDATE users
    SET blocked = FALSE
    WHERE id = user_id_r
      AND blocked = TRUE
      AND NOT EXISTS (   
          SELECT 1
          FROM block
          WHERE blocked_user = user_id_r
            AND end_time > CURRENT_TIMESTAMP
      );

    GET DIAGNOSTICS rows_updated = ROW_COUNT;

    
    IF rows_updated > 0 THEN
        
        INSERT INTO notification (description, general_user_id, notification_type)
        VALUES ('The time has passed, you have been unblocked!', user_id_r, 'Unblocked'::notification_type_enum);
    END IF;

END;
$$;

CREATE OR REPLACE FUNCTION unsubscribe_user(user_id_r INTEGER)
RETURNS VOID
LANGUAGE plpgsql
AS
$$
DECLARE
    rows_updated INTEGER;
BEGIN
    
    UPDATE users
    SET subscribed = FALSE
    WHERE id = user_id_r
    AND subscribed = TRUE
    AND NOT EXISTS (   
        SELECT 1
        FROM subscription
        WHERE subscription.user_id = user_id_r
        AND end_time > CURRENT_TIMESTAMP
    );

    GET DIAGNOSTICS rows_updated = ROW_COUNT;

    
    IF rows_updated > 0 THEN
        
        INSERT INTO notification (description, general_user_id, notification_type)
        VALUES ('Subscription end reached!', user_id_r, 'Subscription End'::notification_type_enum);
    END IF;
END;
$$;

CREATE OR REPLACE FUNCTION unadvertise_auction(auction_id INTEGER)
RETURNS VOID
LANGUAGE plpgsql
AS
$$
DECLARE
    rows_updated INTEGER;
    user_id INTEGER;
BEGIN

    
    UPDATE auction
    SET advertised = FALSE
    WHERE id = auction_id
    AND advertised = TRUE
    AND NOT EXISTS (   
        SELECT 1
        FROM advertisement
        WHERE advertisement.auction_id = auction.id
        AND end_time > CURRENT_TIMESTAMP
    );

    GET DIAGNOSTICS rows_updated = ROW_COUNT;

    
    IF rows_updated > 0 THEN
        SELECT auction.seller_id INTO user_id
        FROM auction
        WHERE id = auction_id;

        
        INSERT INTO notification (description, general_user_id, notification_type, auction)
        VALUES ('Advertisement end reached!', user_id, 'Advertisement End'::notification_type_enum, auction_id);
    END IF;
END;
$$;

CREATE OR REPLACE FUNCTION dailyChecks()
RETURNS void
LANGUAGE plpgsql
AS
$$
DECLARE
    users RECORD;
    auction RECORD;
BEGIN
    
    FOR users IN
        SELECT id
        FROM users
        WHERE blocked = TRUE
    LOOP
        PERFORM unblock_user(users.id);
    END LOOP;

    
    FOR users IN
        SELECT id
        FROM users
        WHERE subscribed = TRUE
    LOOP
        PERFORM unsubscribe_user(users.id);
    END LOOP;

    
    FOR auction IN
        SELECT id
        FROM auction
        WHERE advertised = TRUE
    LOOP
        PERFORM unadvertise_auction(auction.id);
    END LOOP;

    FOR auction IN
        SELECT id
        FROM auction
        WHERE auction_state = 'Active'
    LOOP
        PERFORM finish_auction(auction.id);
    END LOOP;

END;
$$;
