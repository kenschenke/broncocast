import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import { mapProfilePasswordProps, mapProfilePasswordDispatch } from '../maps/Profile/ProfilePassword.map';
import { DuxForm, DuxInput } from 'duxform';

class ProfilePasswordUi extends React.Component {
    constructor(props) {
        super(props);
    }

    componentDidMount() {
        this.props.init();
    }

    render() {
        let currentPwdHelpClass = 'invisible', currentPwdHelpText = '&nbsp;';
        if (this.props.validatingCurrentPwd) {
            currentPwdHelpClass = 'text-muted';
            currentPwdHelpText = 'Checking current password';
        } else if (!this.props.currentPwdValid) {
            currentPwdHelpClass = 'text-danger';
            currentPwdHelpText = 'Current password does not match';
        }
        return (
            <div>
                <h4>Change Password</h4>
                <DuxForm name="profilepwd">
                    <div className="form-group">
                        <label>Current Password</label>
                        <DuxInput name="currentpwd"
                                  type="password"
                                  className="form-control"
                                  onBlur={() => this.props.checkCurrentPwd(this.props.currentPwd)}
                                  onValidate={value => value.trim().length < 1 ? 'Current password cannot be empty' : undefined}
                        />
                        <small className={currentPwdHelpClass} style={{height:'.75em'}}>{currentPwdHelpText}</small>
                    </div>
                    <div className="form-group">
                        <label>New Password</label>
                        <DuxInput name="newpwd1"
                                  type="password"
                                  className="form-control"
                                  onValidate={value => value.trim().length < 4 ? 'Password too short' : undefined}
                        />
                        <small className={'text-danger' + (this.props.newPwd1Valid ? ' invisible' : '')} style={{height:'.75em'}}>
                            Password not long enough
                        </small>
                    </div>
                    <div className="form-group">
                        <label>Confirm Password</label>
                        <DuxInput name="newpwd2"
                                  type="password"
                                  className="form-control"
                                  onValidate={value => (value.trim().length && value.trim() === this.props.newPwd1) ? undefined : 'Passwords do not match'}
                        />
                        <small className={'text-danger' + (this.props.newPwd2Valid ? ' invisible' : '')} style={{height:'.75em'}}>
                            New passwords do not match
                        </small>
                    </div>
                    <button type="button"
                            className="btn btn-secondary"
                            disabled={!this.props.formValid}
                            onClick={() => this.props.changePassword(this.props.newPwd1)}
                    >
                        Change Password
                    </button>
                </DuxForm>
            </div>
        );
    }
}

ProfilePasswordUi.propTypes = {
    formValid: PropTypes.bool.isRequired,
    currentPwd: PropTypes.string.isRequired,
    currentPwdValid: PropTypes.bool.isRequired,
    validatingCurrentPwd: PropTypes.bool.isRequired,
    newPwd1: PropTypes.string.isRequired,
    newPwd1Valid: PropTypes.bool.isRequired,
    newPwd2Valid: PropTypes.bool.isRequired,

    changePassword: PropTypes.func.isRequired,
    checkCurrentPwd: PropTypes.func.isRequired,
    init: PropTypes.func.isRequired
};

export const ProfilePassword = connect(mapProfilePasswordProps, mapProfilePasswordDispatch)(ProfilePasswordUi);
