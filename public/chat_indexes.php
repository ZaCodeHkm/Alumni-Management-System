-- Chat Feature Performance Indexes
-- Run this once to optimize chat queries

-- Index for message queries (conversation lookup and ordering)
CREATE INDEX idx_message_conversation ON message(conversation_id, sent_at);

-- Index for finding user's conversations
CREATE INDEX idx_conv_participant_user ON conversation_participant(user_id);

-- Index for user search functionality (removed is_verified since column doesn't exist)
CREATE INDEX idx_user_search ON user(is_active, name, email);

-- Index for message sender lookup
CREATE INDEX idx_message_sender ON message(sender_id);