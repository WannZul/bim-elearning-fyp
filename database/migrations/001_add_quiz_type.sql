USE bim_elearning;

SET @column_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'quiz_scores'
      AND COLUMN_NAME = 'quiz_type'
);

SET @add_column_sql = IF(
    @column_exists = 0,
    "ALTER TABLE quiz_scores ADD COLUMN quiz_type VARCHAR(20) NOT NULL DEFAULT 'legacy' AFTER time_taken",
    'SELECT 1'
);
PREPARE add_column_statement FROM @add_column_sql;
EXECUTE add_column_statement;
DEALLOCATE PREPARE add_column_statement;

SET @type_index_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'quiz_scores'
      AND INDEX_NAME = 'idx_quiz_scores_type_rank'
);

SET @add_type_index_sql = IF(
    @type_index_exists = 0,
    'CREATE INDEX idx_quiz_scores_type_rank ON quiz_scores (quiz_type, score, time_taken)',
    'SELECT 1'
);
PREPARE add_type_index_statement FROM @add_type_index_sql;
EXECUTE add_type_index_statement;
DEALLOCATE PREPARE add_type_index_statement;

SET @history_index_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'quiz_scores'
      AND INDEX_NAME = 'idx_quiz_scores_user_history'
);

SET @add_history_index_sql = IF(
    @history_index_exists = 0,
    'CREATE INDEX idx_quiz_scores_user_history ON quiz_scores (user_id, created_at)',
    'SELECT 1'
);
PREPARE add_history_index_statement FROM @add_history_index_sql;
EXECUTE add_history_index_statement;
DEALLOCATE PREPARE add_history_index_statement;
