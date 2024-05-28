CREATE TABLE submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    title_slug VARCHAR(255) NOT NULL,
    status VARCHAR(50) NOT NULL,
    timestamp INT NOT NULL,
    lang VARCHAR(50),
    submission_id INT
);

CREATE TABLE problems (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    title_slug VARCHAR(255) NOT NULL,
    difficulty VARCHAR(50) NOT NULL,
    memo TEXT,
    UNIQUE (title_slug)
);

CREATE TABLE problem_tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    problem_id INT NOT NULL,
    tag VARCHAR(50) NOT NULL,
    FOREIGN KEY (problem_id) REFERENCES problems(id)
);
