-- Copyright (C) ---Put here your own copyright and developer email---
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see https://www.gnu.org/licenses/.


-- BEGIN MODULEBUILDER INDEXES
ALTER TABLE llx_college_inscriptions ADD INDEX idx_college_inscriptions_rowid (rowid);
ALTER TABLE llx_college_inscriptions ADD INDEX idx_college_inscriptions_ref (ref);
ALTER TABLE llx_college_inscriptions ADD INDEX idx_college_inscriptions_fk_class (fk_class);
ALTER TABLE llx_college_inscriptions ADD INDEX idx_college_inscriptions_fk_student (fk_student);
ALTER TABLE llx_college_inscriptions ADD INDEX idx_college_inscriptions_fk_tutor (fk_tutor);
ALTER TABLE llx_college_inscriptions ADD CONSTRAINT llx_college_inscriptions_fk_user_creat FOREIGN KEY (fk_user_creat) REFERENCES llx_user(rowid);
ALTER TABLE llx_college_inscriptions ADD INDEX idx_college_inscriptions_status (status);
-- END MODULEBUILDER INDEXES

--ALTER TABLE llx_college_inscriptions ADD UNIQUE INDEX uk_college_inscriptions_fieldxy(fieldx, fieldy);

--ALTER TABLE llx_college_inscriptions ADD CONSTRAINT llx_college_inscriptions_fk_field FOREIGN KEY (fk_field) REFERENCES llx_college_myotherobject(rowid);

