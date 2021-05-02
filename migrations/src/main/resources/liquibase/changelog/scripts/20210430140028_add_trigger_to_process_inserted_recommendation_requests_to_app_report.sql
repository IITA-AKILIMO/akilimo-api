CREATE TRIGGER after_request_insert
    AFTER INSERT
    ON request_response
    FOR EACH ROW
BEGIN
    CALL process_rec_request(NEW.droid_request, NEW.plumber_request, NEW.created_at, NEW.updated_at);
END;
